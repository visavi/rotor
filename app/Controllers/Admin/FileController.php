<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Classes\Validator;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FileController extends AdminController
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
            abort(403, __('errors.forbidden'));
        }

        $request    = request();
        $this->file = ltrim(check($request->input('file')), '/');
        $this->path = rtrim(check($request->input('path')), '/');

        if (empty($this->path) ||
            ! file_exists(RESOURCES . '/views/' . $this->path) ||
            ! is_dir(RESOURCES . '/views/' . $this->path) ||
            Str::contains($this->path, '.') ||
            Str::startsWith($this->path, '/')
        ) {
            $this->path = null;
        }
    }

    /**
     * Главная страница
     *
     * @return string
     */
    public function index(): string
    {
        $path  = $this->path;
        $elements = preg_grep('/^([^.])/', scandir(RESOURCES . '/views/' . $path . $this->file, SCANDIR_SORT_ASCENDING));

        $folders = [];
        $files   = [];

        foreach ($elements as $element) {
            if (is_dir(RESOURCES . '/views/' . $path . '/' . $element)) {
                $folders[] = $element;
            } else {
                $files[] = $element;
            }
        }

        $files = array_merge($folders, $files);

        $directories = explode('/', (string) $path);

        return view('admin/files/index', compact('files', 'path', 'directories'));
    }

    /**
     * Редактирование файла
     *
     * @param Request   $request
     * @param Validator $validator
     *
     * @return string
     */
    public function edit(Request $request, Validator $validator): string
    {
        $path     = $this->path;
        $file     = $path ? '/' . $this->file : $this->file;
        $writable = is_writable(RESOURCES . '/views/' . $path . $file . '.blade.php');

        if (($this->path && ! preg_match('#^([a-z0-9_\-/]+|)$#', $this->path))
            || ! preg_match('#^[a-z0-9_\-/]+$#', $this->file)
        ) {
            abort(404, __('admin.files.file_invalid'));
        }

        if (! file_exists(RESOURCES . '/views/' . $this->path . $file . '.blade.php')) {
            abort(404, __('admin.files.file_not_exist'));
        }

        if ($request->isMethod('post')) {
            $msg = $request->input('msg');

            $validator->equal($request->input('token'), $_SESSION['token'], __('validator.token'))
                ->true($writable, ['msg' => __('admin.files.writable')]);

            if ($validator->isValid()) {
                file_put_contents(RESOURCES . '/views/' . $this->path . $file . '.blade.php', $msg);

                setFlash('success', __('admin.files.file_success_saved'));
                redirect('/admin/files/edit?path=' . $this->path . '&file=' . $this->file);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $contest = file_get_contents(RESOURCES . '/views/' . $path . $file . '.blade.php');

        return view('admin/files/edit', compact('contest', 'path', 'file', 'writable'));
    }

    /**
     * Создание файла
     *
     * @param Request   $request
     * @param Validator $validator
     *
     * @return string
     */
    public function create(Request $request, Validator $validator): string
    {
        if (! is_writable(RESOURCES . '/views/' . $this->path)) {
            abort('default', __('admin.files.directory_not_writable', ['dir' => $this->path]));
        }

        if ($request->isMethod('post')) {
            $filename = check($request->input('filename'));
            $dirname  = check($request->input('dirname'));

            $fileName = $this->path ? '/' . $filename : $filename;
            $dirName  = $this->path ? '/' . $dirname : $dirname;

            $validator->equal($request->input('token'), $_SESSION['token'], __('validator.token'));

            if ($filename) {
                $validator->length($filename, 1, 30, ['filename' => __('admin.files.file_required')]);
                $validator->false(file_exists(RESOURCES . '/views/' . $this->path . $fileName . '.blade.php'), ['filename' => __('admin.files.file_exist')]);
                $validator->regex($filename, '|^[a-z0-9_\-]+$|', ['filename' => __('admin.files.file_invalid')]);
            } else {
                $validator->length($dirname, 1, 30, ['dirname' => __('admin.files.directory_required')]);
                $validator->false(file_exists(RESOURCES . '/views/' . $this->path . $dirName), ['dirname' => __('admin.files.directory_exist')]);
                $validator->regex($dirname, '|^[a-z0-9_\-]+$|', ['dirname' => __('admin.files.directory_invalid')]);
            }

            if ($validator->isValid()) {
                if ($filename) {
                    file_put_contents(RESOURCES . '/views/' . $this->path . $fileName . '.blade.php', '');
                    chmod(RESOURCES.'/views/' . $this->path . $fileName . '.blade.php', 0666);

                    setFlash('success', __('admin.files.file_success_created'));
                    redirect('/admin/files/edit?path=' . $this->path . '&file=' . $filename);
                } else {
                    $old = umask(0);
                    mkdir(RESOURCES . '/views/' . $this->path . $dirName, 0777, true);
                    umask($old);

                    setFlash('success', __('admin.files.directory_success_created'));
                    redirect('/admin/files?path=' . $this->path . $dirName);
                }
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/files/create', ['path' => $this->path]);
    }

    /**
     * Удаление файла
     *
     * @param Request   $request
     * @param Validator $validator
     *
     * @return void
     */
    public function delete(Request $request, Validator $validator): void
    {
        if (! is_writable(RESOURCES . '/views/' . $this->path)) {
            abort('default', __('admin.files.directory_not_writable', ['dir' => $this->path]));
        }

        $filename = check($request->input('filename'));
        $dirname  = check($request->input('dirname'));

        $fileName = $this->path ? '/' . $filename : $filename;
        $dirName  = $this->path ? '/' . $dirname : $dirname;

        $validator->equal($request->input('token'), $_SESSION['token'], __('validator.token'));

        if ($filename) {
            $validator->true(file_exists(RESOURCES . '/views/' . $this->path . $fileName . '.blade.php'), __('admin.files.file_not_exist'));
            $validator->regex($filename, '|^[a-z0-9_\-]+$|', __('admin.files.file_invalid'));
        } else {
            $validator->true(file_exists(RESOURCES . '/views/' . $this->path . $dirName), __('admin.files.directory_not_exist'));
            $validator->regex($dirname, '|^[a-z0-9_\-]+$|', __('admin.files.directory_invalid'));
        }

        if ($validator->isValid()) {
            if ($filename) {
                unlink(RESOURCES . '/views/' . $this->path . $fileName . '.blade.php');
                setFlash('success', __('admin.files.file_success_deleted'));
            } else {
                deleteDir(RESOURCES . '/views/' . $this->path . $dirName);
                setFlash('success', __('admin.files.directory_success_deleted'));
            }
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/files?path=' . $this->path);
    }
}
