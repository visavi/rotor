<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\SendMailJob;
use Carbon\CarbonImmutable;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use Throwable;

class MailService
{
    /**
     * Ставит письмо в очередь
     *
     * Соединение берётся из QUEUE_CONNECTION: при database письмо ждёт
     * воркера и получает повторы, при sync уходит сразу в этом же запросе.
     * Второе нужно там, где крон запускается раз в час: ждать доставки
     * столько же пользователю нельзя
     */
    public function queue(string $view, array $data, int $delayMinutes = 0): void
    {
        $job = SendMailJob::dispatch($view, $data);

        if ($delayMinutes > 0) {
            $job->delay(now()->addMinutes($delayMinutes));
        }
    }

    /**
     * Отправляет письмо, пробрасывая ошибку наружу
     *
     * @throws Throwable
     */
    public function sendOrFail(string $view, array $data): void
    {
        try {
            $this->deliver($view, $data);
        } catch (Throwable $e) {
            Log::error('Mail send failed', [
                'view'      => $view,
                'to'        => $data['to'] ?? null,
                'subject'   => $data['subject'] ?? null,
                'exception' => $e->getMessage(),
            ]);

            throw $e;
        }

        $this->markSuccess();
    }

    /**
     * Отправляет уведомление на email
     */
    public function send(string $view, array $data): bool
    {
        try {
            $this->deliver($view, $data);
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
     * Шаблоны письма: HTML и текстовая версия рядом с ним
     *
     * Текст лежит в mailer/text с тем же именем. Без части text/plain
     * спам-фильтры занижают рейтинг, а часть клиентов показывает пустоту
     *
     * @return array<string, string>
     */
    private function views(string $view): array
    {
        $views = ['html' => $view];
        $text = preg_replace('#^mailer\\.#', 'mailer.text.', $view);

        if ($text !== $view && View::exists($text)) {
            $views['text'] = $text;
        }

        return $views;
    }

    /**
     * Собирает и отправляет письмо
     *
     * @throws Throwable
     */
    private function deliver(string $view, array $data): void
    {
        $views = $this->views($view);

        Mail::send($views, $data, static function (Message $message) use ($data) {
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
