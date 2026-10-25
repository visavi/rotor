<?php

declare(strict_types=1);

namespace App\Services;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use LengthException;
use Psr\Http\Message\ResponseInterface;

/**
 * Картинка ссылки из og:image чужой страницы
 *
 * Адрес приходит от пользователя, а запрос уходит с сервера: без проверки
 * им ходили во внутреннюю сеть и к метаданным облака (169.254.169.254).
 * Поэтому каждый шаг, включая редиректы, проверяется заново, а соединение
 * идёт на проверенный IP — иначе второй DNS-ответ подменил бы адрес
 */
class OgImageService
{
    /**
     * Сколько редиректов пройти: http → https и www обычно хватает двух
     */
    private const int MAX_REDIRECTS = 3;

    /**
     * Сколько читать: og:image стоит в <head>, большой ответ не съест память
     */
    private const int MAX_BYTES = 512 * 1024;

    /**
     * Возвращает адрес картинки или null
     */
    public function find(string $url): ?string
    {
        for ($step = 0; $step <= self::MAX_REDIRECTS; $step++) {
            $resolve = $this->resolve($url);

            if ($resolve === null) {
                return null;
            }

            $sink = fopen('php://temp', 'w+b');
            $head = null;

            try {
                $response = Http::withOptions([
                    'allow_redirects' => false,
                    'curl'            => $resolve ? [CURLOPT_RESOLVE => [$resolve]] : [],
                    'sink'            => $sink,
                    'on_headers'      => static function (ResponseInterface $response) use (&$head) {
                        $head = $response;
                    },
                    'progress' => static function (int $total, int $downloaded) {
                        if ($downloaded > self::MAX_BYTES) {
                            throw new LengthException('Response is too large');
                        }
                    },
                ])
                    ->withUserAgent('Mozilla/5.0')
                    ->timeout(5)
                    ->get($url);

                $status = $response->status();
                $location = $response->header('Location');
                $html = substr($response->body(), 0, self::MAX_BYTES);
            } catch (ConnectionException|GuzzleException) {
                // Оборвано по лимиту или времени: заголовки пришли, а og:image в начале страницы
                if (! $head instanceof ResponseInterface || ! $sink) {
                    return null;
                }

                $status = $head->getStatusCode();
                $location = $head->getHeaderLine('Location');
                $html = (string) stream_get_contents($sink, self::MAX_BYTES, 0);
            }

            if ($status >= 300 && $status < 400 && $location !== '') {
                $url = $this->absolute($location, $url);

                continue;
            }

            if ($status !== 200) {
                return null;
            }

            if (preg_match('/<meta[^>]+property=["\']og:image["\'][^>]+content=["\'](https?:[^"\']+)["\']/i', $html, $match)) {
                return $match[1];
            }

            return null;
        }

        return null;
    }

    /**
     * Проверяет адрес: http или https, стандартный порт, все IP хоста публичные
     *
     * @return string|null запись CURLOPT_RESOLVE «имя:порт:IP», пустая строка для IP в адресе, null — нельзя
     */
    private function resolve(string $url): ?string
    {
        $parts = parse_url($url);
        $scheme = strtolower($parts['scheme'] ?? '');
        $host = trim(strtolower($parts['host'] ?? ''), '[]');
        $port = $parts['port'] ?? null;

        // Нестандартный порт — сканирование чужих сервисов, у страниц со ссылками его нет
        if (! in_array($scheme, ['http', 'https'], true) || $host === '' || ($port !== null && ! in_array($port, [80, 443], true))) {
            return null;
        }

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return $this->isPublic($host) ? '' : null;
        }

        $ips = gethostbynamel($host) ?: [];

        if (! $ips || array_filter($ips, fn (string $ip) => ! $this->isPublic($ip))) {
            return null;
        }

        return $host . ':' . ($port ?? ($scheme === 'https' ? 443 : 80)) . ':' . $ips[0];
    }

    /**
     * Адрес из интернета, а не локальной, частной или служебной сети
     */
    private function isPublic(string $ip): bool
    {
        return (bool) filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_GLOBAL_RANGE);
    }

    /**
     * Абсолютный адрес из Location: он бывает относительным
     */
    private function absolute(string $location, string $base): string
    {
        if (preg_match('#^[a-z][a-z0-9+.-]*://#i', $location)) {
            return $location;
        }

        $parts = parse_url($base);
        $scheme = $parts['scheme'] ?? 'http';

        if (str_starts_with($location, '//')) {
            return $scheme . ':' . $location;
        }

        $origin = $scheme . '://' . ($parts['host'] ?? '') . (isset($parts['port']) ? ':' . $parts['port'] : '');

        if (str_starts_with($location, '/')) {
            return $origin . $location;
        }

        $path = $parts['path'] ?? '/';

        return $origin . substr($path, 0, (int) strrpos($path, '/') + 1) . $location;
    }
}
