<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class CheckerController extends AdminController
{
    /**
     * Главная страница
     */
    public function index(): View
    {
        $diff = [];

        if (file_exists(storage_path('framework/cache/checker.php'))) {
            $files = $this->scanFiles(base_path());
            $filesScan = json_decode(file_get_contents(storage_path('framework/cache/checker.php')), true);

            $diff['left'] = array_diff($files, $filesScan);
            $diff['right'] = array_diff($filesScan, $files);
        }

        return view('admin/checkers/index', compact('diff'));
    }

    /**
     * Сканирование сайта
     */
    public function scan(Request $request): RedirectResponse
    {
        if ($request->input('_token') === csrf_token()) {
            $files = $this->scanFiles(base_path());

            file_put_contents(storage_path('framework/cache/checker.php'), json_encode($files));

            setFlash('success', __('admin.checkers.success_crawled'));
        } else {
            setFlash('danger', __('validator.token'));
        }

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

        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            $state[] = $file->getRelativePathname() . ' / ' . dateFixed($file->getMTime(), 'd.m.y H:i', true) . ' / ' . formatSize($file->getSize());
        }

        return $state;
    }
}
