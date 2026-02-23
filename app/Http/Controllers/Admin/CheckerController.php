<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\Finder\Finder;

class CheckerController extends AdminController
{
    private string $filename = 'checker.php';

    /**
     * Главная страница
     */
    public function index(): View
    {
        $diff = [];
        if (Storage::disk('private')->exists($this->filename)) {
            $files = $this->scanFiles(base_path());

            $filesScan = json_decode(Storage::disk('private')->get($this->filename));

            $diff['left'] = array_diff($files, $filesScan);
            $diff['right'] = array_diff($filesScan, $files);
        }

        return view('admin/checkers/index', compact('diff'));
    }

    /**
     * Сканирование сайта
     */
    public function scan(): RedirectResponse
    {
        $files = $this->scanFiles(base_path());

        Storage::disk('private')->put($this->filename, json_encode($files));

        setFlash('success', __('admin.checkers.success_crawled'));

        return redirect('admin/checkers');
    }

    /**
     * Сканирует директорию сайта
     */
    private function scanFiles(string $dir): array
    {
        $state = [];
        $excludeFiles = preg_filter('/^/', '*.', explode(',', setting('nocheck')));

        $finder = new Finder();
        $files = $finder->in($dir)
            ->files()
            ->exclude(basename(storage_path()))
            ->notName($excludeFiles);

        if (file_exists(base_path('.gitignore'))) {
            $files->ignoreVCSIgnored(true);
        }

        foreach ($files as $file) {
            $state[] = $file->getRelativePathname() . ' / ' . dateFixed($file->getMTime(), 'd.m.y H:i:s', true) . ' / ' . formatSize($file->getSize());
        }

        return $state;
    }
}
