<?php

declare(strict_types=1);

namespace App\Services;

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

    public function getNewReleases(GithubService $github): array
    {
        return array_values(array_filter($github->getLatestReleases(), function (array $release) {
            $version = ltrim($release['tag_name'] ?? '', 'v');
            $version = str_replace(['-alpha', '-beta', '-rc'], '', $version);

            return version_compare(ROTOR_VERSION, $version, '<') && ! empty($release['assets']);
        }));
    }

    /**
     * Находит asset релиза по тегу (источник — кешированный список GitHub)
     *
     * Upgrade-архив (без vendor) берём только для патч-релиза — когда мажор и минор
     * совпадают (14.0.0 → 14.0.1), и только если он реально приложен к релизу.
     * Смена минора/мажора или отсутствие upgrade — полный архив с vendor: минор и
     * мажор могут тянуть новые зависимости, vendor несовместим.
     */
    public function findAsset(GithubService $github, string $tag): ?array
    {
        foreach ($github->getLatestReleases() as $release) {
            if (($release['tag_name'] ?? null) === $tag) {
                return $this->selectAsset($release['assets'] ?? [], $tag);
            }
        }

        return null;
    }

    /**
     * Выбирает архив релиза: upgrade (без vendor) для патча в той же линии
     * мажор.минор, иначе полный. Источник — список assets релиза.
     */
    public function selectAsset(array $assets, string $tag): ?array
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
