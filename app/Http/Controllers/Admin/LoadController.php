<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Classes\Validator;
use App\Models\Down;
use App\Models\File;
use App\Models\Load;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoadController extends AdminController
{
    /**
     * Главная страница
     *
     * @return View
     */
    public function index(): View
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
     *
     * @return RedirectResponse
     */
    public function create(Request $request, Validator $validator): RedirectResponse
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, __('errors.forbidden'));
        }

        $name = $request->input('name');

        $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
            ->length($name, 3, 50, ['title' => __('validator.text')]);

        if ($validator->isValid()) {
            $max = Load::query()->max('sort') + 1;

            /** @var Load $load */
            $load = Load::query()->create([
                'name' => $name,
                'sort' => $max,
            ]);

            setFlash('success', __('loads.load_success_created'));

            return redirect('admin/loads/edit/' . $load->id);
        }

        setInput($request->all());
        setFlash('danger', $validator->getErrors());

        return redirect('admin/loads');
    }

    /**
     * Редактирование раздела
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     *
     * @return View|RedirectResponse
     */
    public function edit(int $id, Request $request, Validator $validator)
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, __('errors.forbidden'));
        }

        /** @var Load $load */
        $load = Load::query()->with('children')->find($id);

        if (! $load) {
            abort(404, __('loads.load_not_exist'));
        }

        $loads = Load::query()
            ->where('parent_id', 0)
            ->orderBy('sort')
            ->get();

        if ($request->isMethod('post')) {
            $parent = int($request->input('parent'));
            $name   = $request->input('name');
            $sort   = int($request->input('sort'));
            $closed = empty($request->input('closed')) ? 0 : 1;

            $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->length($name, 3, 50, ['title' => __('validator.text')])
                ->notEqual($parent, $load->id, ['parent' => __('loads.load_parent_invalid')]);

            if (! empty($parent) && $load->children->isNotEmpty()) {
                $validator->addError(['parent' => __('loads.load_has_subcategories')]);
            }

            if ($validator->isValid()) {
                $load->update([
                    'parent_id' => $parent,
                    'name'      => $name,
                    'sort'      => $sort,
                    'closed'    => $closed,
                ]);

                setFlash('success', __('loads.load_success_edited'));

                return redirect('admin/loads');
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return view('admin/loads/edit', compact('loads', 'load'));
    }

    /**
     * Удаление раздела
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     *
     * @return RedirectResponse
     */
    public function delete(int $id, Request $request, Validator $validator): RedirectResponse
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, __('errors.forbidden'));
        }

        /** @var Load $load */
        $load = Load::query()->with('children')->find($id);

        if (! $load) {
            abort(404, __('loads.load_not_exist'));
        }

        $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
            ->true($load->children->isEmpty(), __('loads.load_has_subcategories'));

        $down = Down::query()->where('category_id', $load->id)->first();
        if ($down) {
            $validator->addError(__('loads.load_has_downs'));
        }

        if ($validator->isValid()) {
            $load->delete();

            setFlash('success', __('loads.load_success_deleted'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        return redirect('admin/loads');
    }

    /**
     * Пересчет данных
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function restatement(Request $request): RedirectResponse
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, __('errors.forbidden'));
        }

        if ($request->input('_token') === csrf_token()) {
            restatement('loads');

            setFlash('success', __('main.success_recounted'));
        } else {
            setFlash('danger', __('validator.token'));
        }

        return redirect('admin/loads');
    }

    /**
     * Просмотр загрузок раздела
     *
     * @param int     $id
     * @param Request $request
     *
     * @return View
     */
    public function load(int $id, Request $request): View
    {
        /** @var Load $category */
        $category = Load::query()->with('parent')->find($id);

        if (! $category) {
            abort(404, __('loads.load_not_exist'));
        }

        $sort = check($request->input('sort', 'time'));

        switch ($sort) {
            case 'rating':
                $order = 'rating';
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
     *
     * @return View|RedirectResponse
     */
    public function editDown(int $id, Request $request, Validator $validator)
    {
        /** @var Down $down */
        $down = Down::query()->find($id);

        if (! $down) {
            abort(404, __('loads.down_not_exist'));
        }

        if ($request->isMethod('post')) {
            $category = int($request->input('category'));
            $title    = $request->input('title');
            $text     = $request->input('text');
            $files    = (array) $request->file('files');
            $links    = (array) $request->input('links');

            $links = array_unique(array_diff($links, ['']));

            /** @var Load $category */
            $category = Load::query()->find($category);

            $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->length($title, 3, 50, ['title' => __('validator.text')])
                ->length($text, 50, 5000, ['text' => __('validator.text')])
                ->notEmpty($category, ['category' => __('loads.load_not_exist')]);

            $duplicate = Down::query()->where('title', $title)->where('id', '<>', $down->id)->count();
            $validator->empty($duplicate, ['title' => __('loads.down_name_exists')]);

            $existFiles = $down->files ? $down->files->count() : 0;
            $validator->notEmpty(count($files) + count($links) + $existFiles , ['files' => __('validator.file_upload_one')]);
            $validator->lte(count($files) + count($links) + $existFiles, setting('maxfiles'), ['files' => __('validator.files_max', ['max' => setting('maxfiles')])]);

            if ($validator->isValid()) {
                $allowExtension = explode(',', setting('allowextload'));

                $rules = [
                    'maxsize'    => setting('fileupload'),
                    'extensions' => $allowExtension,
                    'minweight'  => 100,
                ];

                foreach ($files as $file) {
                    $validator->file($file, $rules, ['files' => __('validator.file_upload_failed')]);
                }

                foreach ($links as $link) {
                    $validator->length($link, 5, 100, ['links' => __('validator.text')])
                        ->url($link, ['links' => __('validator.url')]);

                    if (! in_array(getExtension($link), $allowExtension, true)) {
                        $validator->addError(['links' => __('validator.extension')]);
                    }
                }
            }

            if ($validator->isValid()) {
                $oldDown = $down->replicate();

                $down->update([
                    'category_id' => $category->id,
                    'title'       => $title,
                    'text'        => $text,
                    'links'       => $links ? array_values($links) : null,
                ]);

                if ($down->category->id !== $oldDown->category->id && $down->active) {
                    $down->category->increment('count_downs');
                    $oldDown->category->decrement('count_downs');
                }

                foreach ($files as $file) {
                    $down->uploadAndConvertFile($file);
                }

                if (! $down->active) {
                    $text = textNotice('down_change', ['url' => '/downs/' . $down->id, 'title' => $down->title]);
                    $down->user->sendMessage(null, $text);
                }

                clearCache(['statLoads', 'recentDowns']);
                setFlash('success', __('loads.down_edited_success'));

                return redirect('admin/downs/edit/' . $down->id);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        $cid = 0;
        $categories = Load::query()
            ->where('parent_id', 0)
            ->with('children', 'new', 'children.new')
            ->orderBy('sort')
            ->get();

        return view('admin/loads/edit_down', compact('categories', 'down', 'cid'));
    }

    /**
     * Удаление загрузки
     *
     * @param int     $id
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function deleteDown(int $id, Request $request): RedirectResponse
    {
        /** @var Down $down */
        $down  = Down::query()->find($id);

        if (! $down) {
            abort(404, __('loads.down_not_exist'));
        }

        if (! isAdmin(User::BOSS)) {
            abort(403, __('errors.forbidden'));
        }

        if ($request->input('_token') === csrf_token()) {
            if ($down->active) {
                $down->category->decrement('count_downs');
            }

            $down->comments()->delete();
            $down->delete();

            clearCache(['statLoads', 'recentDowns']);
            setFlash('success', __('loads.down_success_deleted'));
        } else {
            setFlash('danger', __('validator.token'));
        }

        return redirect('admin/loads/' . $down->category_id);
    }

    /**
     * Удаление файла
     *
     * @param int $id
     * @param int $fid
     *
     * @return RedirectResponse
     */
    public function deleteFile(int $id, int $fid): RedirectResponse
    {
        /** @var Down $down */
        $down = Down::query()->find($id);

        if (! $down) {
            abort(404, __('loads.down_not_exist'));
        }

        /** @var File $file */
        $file = $down->files()->find($fid);

        if (! $file) {
            abort(404, __('loads.down_not_exist'));
        }

        deleteFile(public_path($file->hash));

        setFlash('success', __('loads.file_deleted_success'));
        $file->delete();

        return redirect('admin/downs/edit/' . $down->id);
    }

    /**
     * Новые публикации
     *
     * @return View
     */
    public function new(): View
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
     *
     * @return RedirectResponse
     */
    public function publish(int $id, Request $request): RedirectResponse
    {
        /** @var Down $down */
        $down  = Down::query()->find($id);

        if (! $down) {
            abort(404, __('loads.down_not_exist'));
        }

        if ($request->input('_token') === csrf_token()) {
            $active = $down->active ^ 1;

            $down->update([
                'active'     => $active,
                'updated_at' => SITETIME,
                'created_at' => SITETIME,
            ]);

            if ($active) {
                $status = __('loads.down_success_published');
                $down->category->increment('count_downs');
                $text = textNotice('down_publish', ['url' => '/downs/' . $down->id, 'title' => $down->title]);
            } else {
                $status = __('loads.down_success_unpublished');
                $down->category->decrement('count_downs');
                $text = textNotice('down_unpublish', ['url' => '/downs/' . $down->id, 'title' => $down->title]);
            }

            $down->user->sendMessage(null, $text);

            clearCache(['statLoads', 'recentDowns']);
            setFlash('success', $status);
        } else {
            setFlash('danger', __('validator.token'));
        }

        return redirect('admin/downs/edit/' . $down->id);
    }
}
