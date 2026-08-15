<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Module;
use FilesystemIterator;
use Illuminate\Support\Facades\Http;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use ZipArchive;

class UpgradeService
{
    private array $excluded = [
        '.env',
        'storage',
        'public/uploads',
        'modules',
        'app/hooks.php',
    ];

    private array $writableDirs = [
        'app',
        'bootstrap',
        'database',
        'public/assets',
        'resources',
        'routes',
        'vendor',
    ];

    /**
     * Релизы новее текущей версии, доступные для установки
     *
     * Следующий мажор скрывается, пока сайт не дошёл до последнего релиза своей
     * линии: мосты совместимости и промежуточные upgrade-миграции живут в минорах,
     * и прыжок 14.0 → 15.0 сносит модули вместе с сайтом.
     */
    public function getNewReleases(GithubService $github): array
    {
        $releases = array_values(array_filter($github->getLatestReleases(), function (array $release) {
            return version_compare(ROTOR_VERSION, $this->releaseVersion($release), '<') && ! empty($release['assets']);
        }));

        if (! $this->requiredBeforeMajor($github)) {
            return $releases;
        }

        return array_values(array_filter($releases, function (array $release) {
            return $this->major($this->releaseVersion($release)) === $this->major(ROTOR_VERSION);
        }));
    }

    /**
     * Версия, до которой нужно обновиться перед переходом на следующий мажор,
     * либо null — переход не заблокирован
     */
    public function requiredBeforeMajor(GithubService $github): ?string
    {
        $currentMajor = $this->major(ROTOR_VERSION);
        $latestInMajor = null;
        $hasNextMajor = false;

        foreach ($github->getLatestReleases() as $release) {
            if (empty($release['assets'])) {
                continue;
            }

            $version = $this->releaseVersion($release);

            if ($this->major($version) === $currentMajor) {
                if ($latestInMajor === null || version_compare($version, $latestInMajor, '>')) {
                    $latestInMajor = $version;
                }
            } elseif (version_compare($version, ROTOR_VERSION, '>')) {
                $hasNextMajor = true;
            }
        }

        if (! $hasNextMajor || $latestInMajor === null) {
            return null;
        }

        return version_compare(ROTOR_VERSION, $latestInMajor, '<') ? $latestInMajor : null;
    }

    /**
     * Разрешено ли обновление до указанного тега
     */
    public function canUpgradeTo(string $tag, GithubService $github): bool
    {
        $version = ltrim($tag, 'v');

        if ($this->major($version) === $this->major(ROTOR_VERSION)) {
            return true;
        }

        return $this->requiredBeforeMajor($github) === null;
    }

    /**
     * Установленные модули, собранные под предыдущий мажор ядра
     *
     * requires задаёт нижнюю границу, верхней у модуля нет, поэтому единственный
     * доступный признак — мажор, под который модуль собирался. Список носит
     * предупредительный характер: обновление он не блокирует.
     *
     * @return array<int, array{name: string, version: string, requires: string}>
     */
    public function outdatedModules(string $tag): array
    {
        $targetMajor = $this->major(ltrim($tag, 'v'));

        if ($targetMajor === $this->major(ROTOR_VERSION)) {
            return [];
        }

        $modules = [];

        foreach (Module::query()->orderBy('name')->get() as $module) {
            $configFile = base_path('modules/' . $module->name . '/module.php');

            if (! file_exists($configFile)) {
                continue;
            }

            $config = include $configFile;
            $requires = (string) ($config['requires'] ?? '');

            if ($requires === '' || version_compare($this->major($requires), $targetMajor, '>=')) {
                continue;
            }

            $modules[] = [
                'name'     => $config['name'] ?? $module->name,
                'version'  => (string) ($config['version'] ?? $module->version),
                'requires' => $requires,
            ];
        }

        return $modules;
    }

    /**
     * Версия релиза без префикса и суффикса предрелиза
     */
    private function releaseVersion(array $release): string
    {
        $version = ltrim($release['tag_name'] ?? '', 'v');

        return str_replace(['-alpha', '-beta', '-rc'], '', $version);
    }

    /**
     * Мажорная часть версии (14.2.2 → "14")
     */
    private function major(string $version): string
    {
        return explode('.', $version)[0];
    }

    /**
     * Находит asset релиза по тегу (источник — кешированный список GitHub)
     *
     * Upgrade-архив (без vendor) берём только для патч-релиза — когда мажор и минор
     * совпадают (14.0.0 → 14.0.1), и только если он реально приложен к релизу.
     * Смена минора/мажора или отсутствие upgrade — полный архив с vendor: минор и
     * мажор могут тянуть новые зависимости, vendor несовместим.
     */
    public function findAsset(GithubService $github, string $tag, bool $forceFull = false): ?array
    {
        foreach ($github->getLatestReleases() as $release) {
            if (($release['tag_name'] ?? null) === $tag) {
                return $this->selectAsset($release['assets'] ?? [], $tag, $forceFull);
            }
        }

        return null;
    }

    /**
     * Делит assets релиза на полный (с vendor) и upgrade-архив (без vendor)
     */
    public function splitAssets(array $assets): array
    {
        $full = null;
        $upgrade = null;

        foreach ($assets as $asset) {
            $name = $asset['name'] ?? '';

            if (! str_ends_with($name, '.zip')) {
                continue;
            }

            if (str_ends_with($name, '_upgrade.zip')) {
                $upgrade = $asset;
            } else {
                $full = $asset;
            }
        }

        return ['full' => $full, 'upgrade' => $upgrade];
    }

    /**
     * Выбирает архив релиза: upgrade (без vendor) для патча в той же линии
     * мажор.минор, иначе полный. $forceFull принудительно берёт полный архив
     * (запасной вариант, когда патч сменил composer-зависимости).
     */
    public function selectAsset(array $assets, string $tag, bool $forceFull = false): ?array
    {
        ['full' => $full, 'upgrade' => $upgrade] = $this->splitAssets($assets);

        if ($forceFull) {
            return $full ?? $upgrade;
        }

        $samePatchLine = $this->branch(ROTOR_VERSION) === $this->branch(ltrim($tag, 'v'));

        return ($samePatchLine && $upgrade) ? $upgrade : $full;
    }

    /**
     * Линия патчей версии — мажор.минор (14.0.3 → "14.0")
     */
    private function branch(string $version): string
    {
        $parts = explode('.', $version);

        return $parts[0] . '.' . ($parts[1] ?? '0');
    }

    public function checkPermissions(): array
    {
        $failed = [];

        foreach ($this->writableDirs as $dir) {
            $path = base_path($dir);

            if (! file_exists($path)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $item) {
                if ($item->isDir() && ! $item->isWritable()) {
                    $relative = str_replace('\\', '/', substr($item->getPathname(), strlen(base_path()) + 1));
                    $failed[] = $relative;

                    if (count($failed) >= 10) {
                        return $failed;
                    }
                }
            }
        }

        return $failed;
    }

    public function downloadRelease(string $tag, string $url): void
    {
        $tempDir = storage_path('app/temp');

        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $zipPath = $this->zipPath($tag);

        $response = Http::withOptions(['sink' => $zipPath])
            ->timeout(300)
            ->get($url);

        if ($response->failed()) {
            @unlink($zipPath);
            throw new RuntimeException('Download failed: HTTP ' . $response->status());
        }

        $this->extractRelease($zipPath, $tag);

        @unlink($zipPath);
    }

    public function applyUpdate(string $tag): array
    {
        $sourcePath = $this->sourcePath($tag);

        if (! is_dir($sourcePath)) {
            throw new RuntimeException('Update not downloaded');
        }

        $errors = [];
        $this->copyDirectory($sourcePath, base_path(), $errors);

        // Сироты чистим только в vendor: пользовательский код там не живёт,
        // а старые файлы пакетов остаются досягаемыми для автозагрузчика.
        // Если в архиве vendor нет (кривой релиз) — не трогаем.
        if (is_dir($sourcePath . '/vendor')) {
            $this->deleteVendorOrphans($sourcePath . '/vendor');
        }

        return $errors;
    }

    public function cleanup(string $tag): void
    {
        $zip = $this->zipPath($tag);
        $dir = storage_path('app/temp/update-' . $tag);

        if (file_exists($zip)) {
            unlink($zip);
        }

        if (is_dir($dir)) {
            $this->deleteDirectory($dir);
        }
    }

    public function sourcePath(string $tag): string
    {
        $base = storage_path('app/temp/update-' . $tag);

        if (! is_dir($base)) {
            return $base;
        }

        // If ZIP had single root dir (e.g. rotor-14.0.0/), use that
        $dirs = array_filter((array) glob($base . '/*'), 'is_dir');
        $files = array_filter((array) glob($base . '/*'), 'is_file');

        if (count($dirs) === 1 && empty($files)) {
            return array_values($dirs)[0];
        }

        return $base;
    }

    private function zipPath(string $tag): string
    {
        return storage_path('app/temp/update-' . $tag . '.zip');
    }

    private function extractRelease(string $zipPath, string $tag): void
    {
        $extractPath = storage_path('app/temp/update-' . $tag);

        if (is_dir($extractPath)) {
            $this->deleteDirectory($extractPath);
        }

        mkdir($extractPath, 0755, true);

        $zip = new ZipArchive();

        if ($zip->open($zipPath) !== true) {
            throw new RuntimeException('Failed to open ZIP archive');
        }

        $zip->extractTo($extractPath);
        $zip->close();
    }

    private function copyDirectory(string $src, string $dst, array &$errors): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($src, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $relative = substr($item->getPathname(), strlen($src) + 1);
            $relative = str_replace('\\', '/', $relative);

            if ($this->isExcluded($relative)) {
                continue;
            }

            $dest = $dst . DIRECTORY_SEPARATOR . $relative;

            if ($item->isDir()) {
                if (! is_dir($dest)) {
                    mkdir($dest, 0755, true);
                }
            } elseif (! $this->replaceFile($item->getPathname(), $dest)) {
                $errors[] = $relative;
            }
        }
    }

    /**
     * Атомарно заменяет файл: копия рядом + rename.
     * Процессы, читающие старый файл (включая текущий запрос,
     * лениво подгружающий классы из vendor), сохраняют свой inode.
     */
    private function replaceFile(string $src, string $dest): bool
    {
        $tmp = $dest . '.tmp' . getmypid();

        if (! @copy($src, $tmp)) {
            @unlink($tmp);

            return false;
        }

        if (! @rename($tmp, $dest)) {
            @unlink($tmp);

            return false;
        }

        return true;
    }

    /**
     * Удаляет из локального vendor файлы, которых нет в vendor архива
     */
    private function deleteVendorOrphans(string $archiveVendor): void
    {
        $archiveSet = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($archiveVendor, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $item) {
            if ($item->isFile()) {
                $relative = str_replace('\\', '/', substr($item->getPathname(), strlen($archiveVendor) + 1));
                $archiveSet[$relative] = true;
            }
        }

        $vendorPath = base_path('vendor');
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($vendorPath, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $item) {
            if ($item->isFile()) {
                $relative = str_replace('\\', '/', substr($item->getPathname(), strlen($vendorPath) + 1));

                if (! isset($archiveSet[$relative])) {
                    @unlink($item->getPathname());
                }
            }
        }
    }

    private function isExcluded(string $relative): bool
    {
        foreach ($this->excluded as $exclude) {
            if ($relative === $exclude || str_starts_with($relative, $exclude . '/')) {
                return true;
            }
        }

        return false;
    }

    private function deleteDirectory(string $path): void
    {
        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }

        rmdir($path);
    }
}
