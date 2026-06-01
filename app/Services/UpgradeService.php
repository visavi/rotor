<?php

declare(strict_types=1);

namespace App\Services;

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

    public function checkPermissions(): array
    {
        $failed = [];

        foreach ($this->writableDirs as $dir) {
            $path = base_path($dir);

            if (file_exists($path) && ! is_writable($path)) {
                $failed[] = $dir;
            }
        }

        return $failed;
    }

    public function isDownloaded(string $tag): bool
    {
        return is_dir(storage_path('app/temp/update-' . $tag));
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
            new RecursiveDirectoryIterator($src, RecursiveDirectoryIterator::SKIP_DOTS),
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
            } elseif (! @copy($item->getPathname(), $dest)) {
                $errors[] = $relative;
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
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }

        rmdir($path);
    }
}
