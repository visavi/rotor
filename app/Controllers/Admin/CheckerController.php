<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class CheckerController extends AdminController
{
    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();

        if (! isAdmin(User::BOSS)) {
            abort(403, __('errors.forbidden'));
        }
    }

    /**
     * Главная страница
     *
     * @return string
     */
    public function index(): string
    {
        $diff = [];

        if (file_exists(STORAGE . '/caches/checker.php')) {
            $files = $this->scanFiles(BASEDIR);
            $filesScan = json_decode(file_get_contents(STORAGE . '/caches/checker.php'), true);

            $diff['left']  = array_diff($files, $filesScan);
            $diff['right'] = array_diff($filesScan, $files);
        }

        return view('admin/checkers/index', compact('diff'));
    }

    /**
     * Сканирование сайта
     *
     * @param Request $request
     *
     * @return void
     */
    public function scan(Request $request): void
    {
        if ($request->input('token') === $_SESSION['token']) {
            $files = $this->scanFiles(BASEDIR);

            file_put_contents(STORAGE . '/caches/checker.php', json_encode($files));

            setFlash('success', __('admin.checkers.success_crawled'));
        } else {
            setFlash('danger', __('validator.token'));
        }

        redirect('/admin/checkers');
    }

    /**
     * Сканирует директорию сайта
     *
     * @param string $dir
     *
     * @return array
     */
    private function scanFiles($dir): array
    {
        $state = [];
        $excludeFiles = preg_filter('/^/', '*.', explode(',', setting('nocheck')));

        $finder = new Finder();
        $files  = $finder->in($dir)
            ->files()
            ->notName($excludeFiles);

        if (file_exists(BASEDIR . '/.gitignore')) {
            $files->ignoreVCSIgnored(true);
        }

        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            $state[] = $file->getRelativePathname() . ' / ' . dateFixed($file->getMTime(), 'd.m.y H:i') .' / ' . formatSize($file->getSize());
        }

        return $state;
    }
}
