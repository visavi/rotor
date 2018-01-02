<?php

namespace App\Controllers\Admin;

use App\Classes\Request;
use App\Models\User;

class FilesController extends AdminController
{
    private $file;
    private $path;

    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();

        if (! isAdmin(User::BOSS)) {
            abort(403, 'Доступ запрещен!');
        }

        $this->file = check(Request::input('file'));
        $this->path = check(Request::input('path'));

        if (
            ! file_exists(RESOURCES.'/views/'.$this->path) ||
            ! is_dir(RESOURCES.'/views/'.$this->path) ||
            ! ends_with($this->path, '/') ||
            str_contains($this->path, '.') ||
            starts_with($this->path, '/')
        ) {
            $this->path = null;
        }
    }

    /**
     * Главная страница
     */
    public function index()
    {
        $files = preg_grep('/^([^.])/', scandir(RESOURCES.'/views/'.$this->path.$this->file));

        usort($files, function($a, $b) {
            if (is_file(RESOURCES.'/views/'.$this->path.$a) && is_file(APP.'/views/'.$this->path.$b)) {
                return 0;
            }
            return (is_dir(RESOURCES.'/views/'.$this->path.$a)) ? -1 : 1;
        });

        return view('admin/files/index', ['files' => $files, 'path' => $this->path, 'file' => $this->file]);
    }

    /**
     * Редактирование файла
     */
    public function edit()
    {
        return view('admin/files/edit', ['path' => $this->path, 'file' => $this->file]);
    }

    /**
     * Создание файла
     */
    public function create()
    {

    }

    /**
     * Удаление файла
     */
    public function delete()
    {

    }
}
