<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;

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
        $files = $this->scanFiles(BASEDIR);
        $diff  = [];

        if (file_exists(STORAGE . '/temp/checker.dat')) {
            $filesScan = json_decode(file_get_contents(STORAGE . '/temp/checker.dat'));

            $diff['left']  = array_diff($files, $filesScan);
            $diff['right'] = array_diff($filesScan, $files);
        }

        return view('admin/checkers/index', compact('diff'));
    }

    /**
     * Сканирование сайта
     *
     * @param Request $request
     * @return void
     */
    public function scan(Request $request): void
    {
        $token = check($request->input('token'));

        if ($token === $_SESSION['token']) {
            if (is_writable(STORAGE . '/temp')) {
                $files = $this->scanFiles(BASEDIR);

                file_put_contents(STORAGE . '/temp/checker.dat', json_encode($files), LOCK_EX);

                setFlash('success', 'Сайт успешно отсканирован!');
            } else {
                setFlash('danger', 'Директория temp недоступна для записи!');
            }
        } else {
            setFlash('danger', __('validator.token'));
        }

        redirect('/admin/checkers');
    }

    /**
     * Сканирует директорию сайта
     *
     * @param string $dir
     * @return array
     */
    private function scanFiles($dir): array
    {
        static $state;

        $files = preg_grep('/^([^.])/', scandir($dir, SCANDIR_SORT_ASCENDING));

        foreach ($files as $file) {
            if (is_file($dir . '/' . $file)) {
                $ext = getExtension($file);

                if (! in_array($ext, explode(',', setting('nocheck')), true)) {
                    $state[] = $dir . '/' . $file . ' / ' . dateFixed(filemtime($dir . '/' . $file), 'd.m.Y H:i') . ' / ' . formatFileSize($dir . '/' . $file);
                }
            } else {
                $state[] = $dir . '/' . $file;
                $this->scanFiles($dir . '/' . $file);
            }
        }

        return str_replace(BASEDIR, '', $state);
    }
}
