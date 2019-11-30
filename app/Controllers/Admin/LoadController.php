<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Classes\Validator;
use App\Models\Down;
use App\Models\File;
use App\Models\Load;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;

class LoadController extends AdminController
{
    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();

        if (! isAdmin(User::ADMIN)) {
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
        $categories = Load::query()
            ->where('parent_id', 0)
            ->with('children', 'new', 'children.new')
            ->orderBy('sort')
            ->get();

        return view('admin/loads/index', compact('categories'));
    }

    /**
     * Создание раздела
     *
     * @param Request   $request
     * @param Validator $validator
     * @return void
     */
    public function create(Request $request, Validator $validator): void
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, __('errors.forbidden'));
        }

        $token = check($request->input('token'));
        $name  = check($request->input('name'));

        $validator->equal($token, $_SESSION['token'], __('validator.token'))
            ->length($name, 3, 50, ['title' => __('validator.text')]);

        if ($validator->isValid()) {
            $max = Load::query()->max('sort') + 1;

            /** @var Load $load */
            $load = Load::query()->create([
                'name' => $name,
                'sort' => $max,
            ]);

            setFlash('success', 'Новый раздел успешно создан!');
            redirect('/admin/loads/edit/' . $load->id);
        } else {
            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/loads');
    }

    /**
     * Редактирование раздела
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     * @return string
     */
    public function edit(int $id, Request $request, Validator $validator): string
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, __('errors.forbidden'));
        }

        /** @var Load $load */
        $load = Load::query()->with('children')->find($id);

        if (! $load) {
            abort(404, __('loads.category_not_exist'));
        }

        $loads = Load::query()
            ->where('parent_id', 0)
            ->orderBy('sort')
            ->get();

        if ($request->isMethod('post')) {
            $token  = check($request->input('token'));
            $parent = int($request->input('parent'));
            $name   = check($request->input('name'));
            $sort   = check($request->input('sort'));
            $closed = empty($request->input('closed')) ? 0 : 1;

            $validator->equal($token, $_SESSION['token'], __('validator.token'))
                ->length($name, 3, 50, ['title' => __('validator.text')])
                ->notEqual($parent, $load->id, ['parent' => 'Недопустимый выбор родительского раздела!']);

            if (! empty($parent) && $load->children->isNotEmpty()) {
                $validator->addError(['parent' => 'Текущий раздел имеет подразделы!']);
            }

            if ($validator->isValid()) {
                $load->update([
                    'parent_id' => $parent,
                    'name'      => $name,
                    'sort'      => $sort,
                    'closed'    => $closed,
                ]);

                setFlash('success', 'Раздел успешно отредактирован!');
                redirect('/admin/loads');
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/loads/edit', compact('loads', 'load'));
    }

    /**
     * Удаление раздела
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     * @return void
     * @throws Exception
     */
    public function delete(int $id, Request $request, Validator $validator): void
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, __('errors.forbidden'));
        }

        /** @var Load $load */
        $load = Load::query()->with('children')->find($id);

        if (! $load) {
            abort(404, __('loads.category_not_exist'));
        }

        $token = check($request->input('token'));

        $validator->equal($token, $_SESSION['token'], __('validator.token'))
            ->true($load->children->isEmpty(), 'Удаление невозможно! Данный раздел имеет подразделы!');

        $down = Down::query()->where('category_id', $load->id)->first();
        if ($down) {
            $validator->addError('Удаление невозможно! В данном разделе имеются загрузки!');
        }

        if ($validator->isValid()) {
            $load->delete();

            setFlash('success', 'Раздел успешно удален!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/loads');
    }

    /**
     * Пересчет данных
     *
     * @param Request $request
     * @return void
     */
    public function restatement(Request $request): void
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, __('errors.forbidden'));
        }

        $token = check($request->input('token'));

        if ($token === $_SESSION['token']) {
            restatement('loads');

            setFlash('success', __('main.success_recounted'));
        } else {
            setFlash('danger', __('validator.token'));
        }

        redirect('/admin/loads');
    }

    /**
     * Просмотр загрузок раздела
     *
     * @param int     $id
     * @param Request $request
     * @return string
     */
    public function load(int $id, Request $request): string
    {
        /** @var Load $category */
        $category = Load::query()->with('parent')->find($id);

        if (! $category) {
            abort(404, __('loads.category_not_exist'));
        }

        $sort = check($request->input('sort', 'time'));

        switch ($sort) {
            case 'rated':
                $order = 'rated';
                break;
            case 'comments':
                $order = 'count_comments';
                break;
            case 'loads':
                $order = 'loads';
                break;
            default:
                $order = 'created_at';
        }

        $downs = Down::query()
            ->where('category_id', $category->id)
            ->where('active', 1)
            ->orderByDesc($order)
            ->paginate(setting('downlist'))
            ->appends(['sort' => $sort]);

        return view('admin/loads/load', compact('category', 'downs', 'order'));
    }

    /**
     * Редактирование загрузки
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     * @return string
     */
    public function editDown(int $id, Request $request, Validator $validator): string
    {
        /** @var Down $down */
        $down = Down::query()->find($id);

        if (! $down) {
            abort(404, __('loads.down_not_exist'));
        }

        if ($request->isMethod('post')) {
            $token    = check($request->input('token'));
            $category = check($request->input('category'));
            $title    = check($request->input('title'));
            $text     = check($request->input('text'));
            $files    = (array) $request->file('files');

            /** @var Load $category */
            $category = Load::query()->find($category);

            $validator->equal($token, $_SESSION['token'], __('validator.token'))
                ->length($title, 5, 50, ['title' => __('validator.text')])
                ->length($text, 50, 5000, ['text' => __('validator.text')])
                ->notEmpty($category, ['category' => __('loads.category_not_exist')]);

            $duplicate = Down::query()->where('title', $title)->where('id', '<>', $down->id)->count();
            $validator->empty($duplicate, ['title' => __('loads.down_name_exists')]);

            $existFiles = $down->files ? $down->files->count() : 0;
            $validator->lte(count($files) + $existFiles, setting('maxfiles'), ['files' => __('validator.files_max', ['max' => setting('maxfiles')])]);

            if ($validator->isValid()) {
                $rules = [
                    'maxsize'    => setting('fileupload'),
                    'extensions' => explode(',', setting('allowextload')),
                    'minweight'  => 100,
                ];

                foreach ($files as $file) {
                    $validator->file($file, $rules, ['files' => __('validator.failed_upload')]);
                }
            }

            if ($validator->isValid()) {
                $oldDown = $down->replicate();

                $down->update([
                    'category_id' => $category->id,
                    'title'       => $title,
                    'text'        => $text,
                ]);

                if ($down->category->id !== $oldDown->category->id && $down->active) {
                    $down->category->increment('count_downs');
                    $oldDown->category->decrement('count_downs');
                }

                foreach ($files as $file) {
                    $down->uploadFile($file);
                }

                if (! $down->active) {
                    $text = textNotice('down_change', ['url' => '/downs/' . $down->id, 'title' => $down->title]);
                    $down->user->sendMessage(null, $text);
                }

                clearCache(['statLoads', 'recentDowns']);
                setFlash('success', 'Загрузка успешно отредактирована!');
                redirect('/admin/downs/edit/' . $down->id);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $categories = Load::query()
            ->where('parent_id', 0)
            ->with('children', 'new', 'children.new')
            ->orderBy('sort')
            ->get();

        return view('admin/loads/edit_down', compact('categories', 'down'));
    }

    /**
     * Удаление загрузки
     *
     * @param int     $id
     * @param Request $request
     * @return void
     * @throws Exception
     */
    public function deleteDown(int $id, Request $request): void
    {
        $token = check($request->input('token'));

        /** @var Down $down */
        $down  = Down::query()->find($id);

        if (! $down) {
            abort(404, __('loads.down_not_exist'));
        }

        if (! isAdmin(User::BOSS)) {
            abort(403, __('errors.forbidden'));
        }

        if ($token === $_SESSION['token']) {
            if ($down->active) {
                $down->category->decrement('count_downs');
            }

            $down->comments()->delete();
            $down->delete();

            clearCache(['statLoads', 'recentDowns']);
            setFlash('success', 'Загрузка успешно удалена!');
        } else {
            setFlash('danger', __('validator.token'));
        }

        redirect('/admin/loads/' . $down->category_id);
    }

    /**
     * Удаление файла
     *
     * @param int $id
     * @param int $fid
     * @return void
     * @throws Exception
     */
    public function deleteFile(int $id, int $fid): void
    {
        /** @var Down $down */
        $down = Down::query()->find($id);

        if (! $down) {
            abort(404, __('loads.down_not_exist'));
        }

        /** @var File $file */
        $file = File::query()->where('relate_id', $down->id)->find($fid);

        if (! $file) {
            abort(404, __('loads.down_not_exist'));
        }

        deleteFile(HOME . $file->hash);

        setFlash('success', __('loads.file_deleted_success'));
        $file->delete();

        redirect('/admin/downs/edit/' . $down->id);
    }

    /**
     * Новые публикации
     *
     * @return string
     */
    public function new(): string
    {
        $downs = Down::query()
            ->where('active', 0)
            ->orderByDesc('created_at')
            ->with('user', 'category', 'files')
            ->paginate(setting('downlist'));

        return view('admin/loads/new', compact('downs'));
    }

    /**
     * Публикация загрузки
     *
     * @param int     $id
     * @param Request $request
     * @return void
     */
    public function publish(int $id, Request $request): void
    {
        /** @var Down $down */
        $down  = Down::query()->find($id);
        $token = check($request->input('token'));

        if (! $down) {
            abort(404, __('loads.down_not_exist'));
        }

        if ($token === $_SESSION['token']) {
            $active = $down->active ^ 1;

            $down->update([
                'active'     => $active,
                'updated_at' => SITETIME,
            ]);

            if ($active) {
                $type = 'опубликована' ;
                $down->category->increment('count_downs');

                $text = textNotice('down_publish', ['url' => '/downs/' . $down->id, 'title' => $down->title]);
                $down->user->sendMessage(null, $text);

            } else {
                $type = 'снята с публикации';
                $down->category->decrement('count_downs');

                $text = textNotice('down_unpublish', ['url' => '/downs/' . $down->id, 'title' => $down->title]);
                $down->user->sendMessage(null, $text);
            }

            clearCache(['statLoads', 'recentDowns']);
            setFlash('success', 'Загрузка успешно ' . $type . '!');
        } else {
            setFlash('danger', __('validator.token'));
        }

        redirect('/admin/downs/edit/' . $down->id);
    }
}
