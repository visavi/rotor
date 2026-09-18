<?php

declare(strict_types=1);

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class MailService
{
    /**
     * Отправляет уведомление на email
     */
    public function send(string $view, array $data): bool
    {
        try {
            Mail::send($view, $data, static function (Message $message) use ($data) {
                $message->subject($data['subject'])
                    ->to($data['to'])
                    ->from(config('mail.from.address'), config('mail.from.name'));

                if (isset($data['from'])) {
                    [$fromEmail, $fromName] = $data['from'];
                    $message->replyTo($fromEmail, $fromName);
                }

                if (isset($data['unsubscribe'])) {
                    $headers = $message->getHeaders();
                    $headers->addTextHeader(
                        'List-Unsubscribe',
                        '<' . config('app.url') . '/unsubscribe?key=' . $data['unsubscribe'] . '>'
                    );
                }
            });
        } catch (Throwable $e) {
            // Ошибка отправки только возвращалась флагом, и поломка почты
            // оставалась незаметной: пишем в лог и отмечаем для панели
            Log::error('Mail send failed', [
                'view'      => $view,
                'to'        => $data['to'] ?? null,
                'subject'   => $data['subject'] ?? null,
                'exception' => $e->getMessage(),
            ]);

            $this->markFailure($e, $data);

            return false;
        }

        $this->markSuccess();

        return true;
    }

    /**
     * Отмечает неудачную отправку письма
     *
     * Метка лежит файлом, а не в кэше: кэш админ чистит кнопкой в панели,
     * и после чистки поломка почты пропала бы из панели незамеченной
     */
    public function markFailure(Throwable $e, array $data = []): void
    {
        $path = $this->path();

        if (! is_dir(dirname($path))) {
            return;
        }

        file_put_contents($path, json_encode([
            'message' => $e->getMessage(),
            'to'      => $data['to'] ?? null,
            'time'    => time(),
        ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    }

    /**
     * Снимает метку после успешной отправки
     */
    public function markSuccess(): void
    {
        $path = $this->path();

        if (is_file($path)) {
            unlink($path);
        }
    }

    /**
     * Данные последней неудачной отправки
     */
    public function lastFailure(): ?array
    {
        $path = $this->path();

        if (! is_file($path)) {
            return null;
        }

        $failure = json_decode((string) file_get_contents($path), true);

        if (! is_array($failure)) {
            return null;
        }

        $failure['time'] = isset($failure['time'])
            ? CarbonImmutable::createFromTimestamp($failure['time'])
            : null;

        return $failure;
    }

    /**
     * Ломалась ли отправка писем
     */
    public function hasFailure(): bool
    {
        return is_file($this->path());
    }

    /**
     * Путь к метке поломки
     */
    private function path(): string
    {
        return storage_path('framework/mail-failure');
    }
}
