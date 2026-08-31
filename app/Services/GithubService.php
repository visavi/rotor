<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class GithubService
{
    protected string $baseUrl;
    protected int $defaultCacheTtl = 3600;

    /** Достучались ли до источника в этом запросе (null — не ходили, хватило кеша) */
    protected ?bool $available = null;

    public function __construct()
    {
        // Адрес настраиваемый: там, где GitHub недоступен, сайт смотрит на зеркало
        $this->baseUrl = rtrim((string) config('services.rotor.url'), '/') . '/';
    }

    /**
     * Отвечает, доступен ли источник обновлений
     *
     * null — за данными не ходили, хватило кеша
     */
    public function isAvailable(): ?bool
    {
        return $this->available;
    }

    /**
     * Возвращает адрес источника обновлений
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Получает последние коммиты
     */
    public function getLatestCommits(): array
    {
        return $this->cachedFetch('commits', fn () => $this->fetchGitHubData(
            endpoint: 'commits',
            params: ['per_page' => 100]
        ));
    }

    /**
     * Get last release
     */
    public function getLatestRelease(): array
    {
        return $this->getLatestReleases()[0] ?? [];
    }

    /**
     * Get last version
     */
    public function getLatestVersion(): string
    {
        $release = $this->getLatestRelease();

        return $release['tag_name'] ?? '';
    }

    /**
     * Get last version
     */
    public function getLatestVersionClean(): string
    {
        $version = $this->getLatestVersion();
        $clean = ltrim($version, 'v');

        return str_replace(['-alpha', '-beta', '-rc'], '', $clean);
    }

    /**
     * Получает последние релизы
     */
    public function getLatestReleases(): array
    {
        return $this->cachedFetch('releases', fn () => $this->fetchGitHubData(
            endpoint: 'releases',
            params: ['per_page' => 100]
        ));
    }

    /**
     * Отдаёт данные из кэша сразу (даже протухшие), протухший кэш обновляет
     * после отправки ответа. Синхронный запрос к GitHub — только на холодном
     * кэше, чтобы страница не подвисала на каждом промахе.
     */
    protected function cachedFetch(string $key, callable $fetcher): array
    {
        $cached = Cache::get($key);

        // Холодный кэш: тянем синхронно, иначе данных вообще не будет
        if (! is_array($cached)) {
            return $this->store($key, $fetcher);
        }

        // Протухло: отдаём старое, обновляем в фоне после ответа
        if (($cached['cached_at'] ?? 0) < now()->getTimestamp() - $this->defaultCacheTtl) {
            dispatch(fn () => $this->store($key, $fetcher))->afterResponse();
        }

        return $cached['data'] ?? [];
    }

    /**
     * Запрашивает данные и кладёт в кэш вместе с меткой времени
     */
    protected function store(string $key, callable $fetcher): array
    {
        $data = $fetcher();

        Cache::forever($key, [
            'data'      => $data,
            'cached_at' => now()->getTimestamp(),
        ]);

        return $data;
    }

    /**
     * Обращается к GitHub API
     */
    protected function fetchGitHubData(string $endpoint, array $params = []): array
    {
        $headers = [
            'Accept' => 'application/vnd.github+json',
            // GitHub требует User-Agent, а зеркала обычно режут безликие http-клиенты
            'User-Agent' => 'Rotor/' . ROTOR_VERSION,
        ];

        try {
            $response = Http::withHeaders($headers)
                ->timeout(3)
                ->retry(3, 100)
                ->get($this->baseUrl . $endpoint, $params);

            if ($response->failed()) {
                throw new RuntimeException(
                    'GitHub API error: ' . $response->body(),
                    $response->status()
                );
            }

            $this->available = true;

            return $response->json() ?? [];
        } catch (Throwable) {
            $this->available = false;

            return [];
        }
    }
}
