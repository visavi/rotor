<?php

namespace App\Controllers\Admin;

use App\Classes\Request;
use App\Models\User;

class CheckerController extends AdminController
{
    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();

        if (! isAdmin(User::BOSS)) {
            abort(403, 'Доступ запрещен!');
        }
    }

    /**
     * Главная страница
     *
     * @return string
     */
    public function index(): string
    {
        $files = $this->scanFiles('../');
        $files = str_replace('..//', '', $files);
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
     * @return void
     */
    public function scan(): void
    {
        $token = check(Request::input('token'));

        if ($token === $_SESSION['token']) {
            if (is_writable(STORAGE . '/temp')) {
                $files = $this->scanFiles('../');
                $files = str_replace('..//', '', $files);

                file_put_contents(STORAGE . '/temp/checker.dat', json_encode($files), LOCK_EX);

                setFlash('success', 'Сайт успешно отсканирован!');
            } else {
                setFlash('danger', 'Директория temp недоступна для записи!');
            }
        } else {
            setFlash('danger', 'Неверный идентификатор сессии, повторите действие!');
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

                if (! \in_array($ext, explode(',', setting('nocheck')), true)) {
                    $state[] = $dir . '/' . $file . ' / ' . dateFixed(filemtime($dir . '/' . $file), 'd.m.Y H:i') . ' / ' . formatFileSize($dir . '/' . $file);
                }
            } else {
                $state[] = $dir . '/' . $file;
                $this->scanFiles($dir . '/' . $file);
            }
        }

        return $state;
    }
}
