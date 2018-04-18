<?php

namespace App\Controllers\Admin;

use App\Classes\Request;
use App\Classes\Validator;
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
            empty($this->path) ||
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
        $path =  $this->path ? $this->path . '/' : $this->path;

        $files = preg_grep('/^([^.])/', scandir(RESOURCES . '/views/' . $path . $this->file));

        usort($files, function($a, $b) use ($path) {
            if (is_file(RESOURCES . '/views/'. $path . '/' . $a) && is_file(RESOURCES . '/views/' . $path . '/' . $b)) {
                return 0;
            }
            return is_dir(RESOURCES . '/views/' . $path . '/' . $a) ? -1 : 1;
        });

        return view('admin/files/index',compact('files', 'path'));
    }

    /**
     * Редактирование файла
     */
    public function edit()
    {
        if (! preg_match('#^([a-z0-9_\-/]+|)$#', $this->path) || ! preg_match('#^[a-z0-9_\-/]+$#', $this->file)) {
            abort('default', 'Недопустимое название страницы!');
        }

        if (! file_exists(RESOURCES.'/views/'.$this->path.$this->file.'.blade.php')) {
            abort('default', 'Данного файла не существует!');
        }

        if (! is_writable(RESOURCES.'/views/'.$this->path.$this->file.'.blade.php')) {
            abort('default', 'Файл недоступен для записи!');
        }

        if (Request::isMethod('post')) {
            $token = check(Request::input('token'));
            $msg   = Request::input('msg');

            if ($token == $_SESSION['token']) {

                file_put_contents(RESOURCES.'/views/'.$this->path.$this->file.'.blade.php', $msg);

                setFlash('success', 'Файл успешно сохранен!');
                redirect ('/admin/files/edit?path='.$this->path.'&file='.$this->file);

            } else {
                setInput(Request::all());
                setFlash('danger', 'Неверный идентификатор сессии, повторите действие!');
            }
        }

        $contest = file_get_contents(RESOURCES.'/views/'.$this->path.$this->file.'.blade.php');

        return view('admin/files/edit', ['contest' => $contest, 'path' => $this->path, 'file' => $this->file]);
    }

    /**
     * Создание файла
     */
    public function create()
    {
        if (! is_writable(RESOURCES.'/views/'.$this->path)) {
            abort('default', 'Директория '.$this->path.' недоступна для записи!');
        }

        if (Request::isMethod('post')) {
            $token    = check(Request::input('token'));
            $filename = check(Request::input('filename'));
            $dirname  = check(Request::input('dirname'));

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!');

            if ($filename) {
                $validator->length($filename, 1, 30, ['filename' => 'Необходимо ввести название файла!']);
                $validator->false(file_exists(RESOURCES . '/views/' . $this->path . $filename . '.blade.php'), ['filename' => 'Файл с данным названием уже существует!']);
                $validator->regex($filename, '|^[a-z0-9_\-]+$|', ['filename' => 'Недопустимое название файла!']);
            } else {
                $validator->length($dirname, 1, 30, ['dirname' => 'Необходимо ввести название директории!']);
                $validator->false(file_exists(RESOURCES . '/views/' . $this->path . $dirname), ['dirname' => 'Директория с данным названием уже существует!']);
                $validator->regex($dirname, '|^[a-z0-9_\-]+$|', ['dirname' => 'Недопустимое название директории!']);
            }

            if ($validator->isValid()) {

                if ($filename) {

                    file_put_contents(RESOURCES.'/views/'.$this->path.$filename.'.blade.php', '');
                    chmod(RESOURCES.'/views/'.$this->path.$filename.'.blade.php', 0666);

                    setFlash('success', 'Новый файл успешно создан!');
                    redirect('/admin/files/edit?path=' . $this->path . '&file=' . $filename);
                } else {

                    $old = umask(0);
                    mkdir(RESOURCES .'/views/'.$this->path.$dirname, 0777, true);
                    umask($old);

                    setFlash('success', 'Новая директория успешно создана!');
                    redirect('/admin/files?path='. $this->path . $dirname.'/');
                }

            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/files/create', ['path' => $this->path]);
    }

    /**
     * Удаление файла
     */
    public function delete()
    {
        if (! is_writable(RESOURCES.'/views/'.$this->path)) {
            abort('default', 'Директория '.$this->path.' недоступна для записи!');
        }

        $token    = check(Request::input('token'));
        $filename = check(Request::input('filename'));
        $dirname  = check(Request::input('dirname'));

        $validator = new Validator();
        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!');

        if ($filename) {
            $validator->true(file_exists(RESOURCES . '/views/' . $this->path . $filename . '.blade.php'), 'Данного файла не существует!');
            $validator->regex($filename, '|^[a-z0-9_\-]+$|', 'Недопустимое название файла!');
        } else {
            $validator->true(file_exists(RESOURCES . '/views/' . $this->path . $dirname), 'Данной директории не существует!');
            $validator->regex($dirname, '|^[a-z0-9_\-]+$|', 'Недопустимое название директории!');
        }

        if ($validator->isValid()) {

            if ($filename) {
                unlink(RESOURCES .'/views/'.$this->path.$filename.'.blade.php');
                setFlash('success', 'Файл успешно удален!');
            } else {
                removeDir(RESOURCES . '/views/' . $this->path . $dirname);
                setFlash('success', 'Директория успешно удалена!');
            }

        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/files?path='. $this->path);
    }
}
