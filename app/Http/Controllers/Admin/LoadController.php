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
     */
    public function index(): View
    {
        $categories = Load::query()
            ->where('parent_id', 0)
            ->with('children', 'new', 'children.new', 'lastDown.user')
            ->orderBy('sort')
            ->get();

        $new = Down::query()
            ->active(false)
            ->count();

        return view('admin/loads/index', compact('categories', 'new'));
    }

    /**
     * Создание раздела
     */
    public function create(Request $request, Validator $validator): RedirectResponse
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, __('errors.forbidden'));
        }

        $name = $request->input('name');

        $validator->length($name, setting('down_category_min'), setting('down_category_max'), ['title' => __('validator.text')]);

        if ($validator->isValid()) {
            $max = Load::query()->max('sort') + 1;

            $load = Load::query()->create([
                'name' => $name,
                'sort' => $max,
            ]);

            setFlash('success', __('loads.load_success_created'));

            return redirect()->route('admin.loads.edit', ['id' => $load->id]);
        }

        setInput($request->all());
        setFlash('danger', $validator->getErrors());

        return redirect()->route('admin.loads.index');
    }

    /**
     * Редактирование раздела
     */
    public function edit(int $id, Request $request, Validator $validator): View|RedirectResponse
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, __('errors.forbidden'));
        }

        $load = Load::query()->with('children')->find($id);

        if (! $load) {
            abort(404, __('loads.load_not_exist'));
        }

        if ($request->isMethod('post')) {
            $parent = int($request->input('parent'));
            $name = $request->input('name');
            $sort = int($request->input('sort'));
            $closed = empty($request->input('closed')) ? 0 : 1;

            $validator
                ->length($name, setting('down_category_min'), setting('down_category_max'), ['title' => __('validator.text')])
                ->notEqual($parent, $load->id, ['parent' => __('loads.load_parent_invalid')]);

            if ($validator->isValid()) {
                $load->update([
                    'parent_id' => $parent,
                    'name'      => $name,
                    'sort'      => $sort,
                    'closed'    => $closed,
                ]);

                setFlash('success', __('loads.load_success_edited'));

                return redirect()->route('admin.loads.index');
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        $loads = $load->getChildren();

        return view('admin/loads/edit', compact('loads', 'load'));
    }

    /**
     * Удаление раздела
     */
    public function delete(int $id, Validator $validator): RedirectResponse
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, __('errors.forbidden'));
        }

        $load = Load::query()->with('children')->find($id);

        if (! $load) {
            abort(404, __('loads.load_not_exist'));
        }

        $validator->true($load->children->isEmpty(), __('loads.load_has_subcategories'));

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

        return redirect()->route('admin.loads.index');
    }

    /**
     * Пересчет данных
     */
    public function restatement(): RedirectResponse
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, __('errors.forbidden'));
        }

        restatement('loads');

        return redirect()
            ->route('admin.loads.index')
            ->with('success', __('main.success_recounted'));
    }

    /**
     * Просмотр загрузок раздела
     */
    public function load(int $id, Request $request): View
    {
        $category = Load::query()->with('parent')->find($id);

        if (! $category) {
            abort(404, __('loads.load_not_exist'));
        }

        $sort = check($request->input('sort', 'date'));
        $order = check($request->input('order', 'desc'));

        [$sorting, $orderBy] = Down::getSorting($sort, $order);

        $downs = Down::query()
            ->active()
            ->where('category_id', $category->id)
            ->orderBy(...$orderBy)
            ->with('user')
            ->paginate(setting('downlist'))
            ->appends(compact('sort', 'order'));

        return view('admin/loads/load', compact('category', 'downs', 'sorting'));
    }

    /**
     * Редактирование загрузки
     */
    public function editDown(int $id, Request $request, Validator $validator): View|RedirectResponse
    {
        $cid = int($request->input('category'));

        $down = Down::query()->find($id);

        if (! $down) {
            abort(404, __('loads.down_not_exist'));
        }

        $files = File::query()
            ->where('relate_type', Down::$morphName)
            ->where('relate_id', $down->id)
            ->orderBy('created_at')
            ->get();

        if ($request->isMethod('post')) {
            $title = $request->input('title');
            $text = $request->input('text');
            $links = (array) $request->input('links');
            $links = array_unique(array_diff($links, ['']));

            $category = Load::query()->find($cid);

            $validator
                ->length($title, setting('down_title_min'), setting('down_title_max'), ['title' => __('validator.text')])
                ->length($text, setting('down_text_min'), setting('down_text_max'), ['text' => __('validator.text')])
                ->notEmpty($category, ['category' => __('loads.load_not_exist')]);

            $duplicate = Down::query()
                ->where('title', $title)
                ->where('id', '<>', $down->id)
                ->count();

            $validator->empty($duplicate, ['title' => __('loads.down_name_exists')]);

            $validator->notEmpty($files->count() + count($links), ['files' => __('validator.file_upload_one')]);
            $validator->lte($files->count() + count($links), setting('maxfiles'), ['files' => __('validator.files_max', ['max' => setting('maxfiles')])]);

            if ($validator->isValid()) {
                foreach ($links as $link) {
                    $validator->length($link, setting('down_link_min'), setting('down_link_max'), ['links' => __('validator.text')])
                        ->url($link, ['links' => __('validator.url')]);
                }
            }

            if ($validator->isValid()) {
                $oldDown = $down->replicate();
                $links = setting('down_allow_links') ? array_values($links) : null;

                $down->update([
                    'category_id' => $category->id,
                    'title'       => $title,
                    'text'        => $text,
                    'links'       => $links,
                ]);

                if ($down->category->id !== $oldDown->category->id && $down->active) {
                    $down->category->increment('count_downs');
                    $oldDown->category->decrement('count_downs');
                }

                if (! $down->active) {
                    $text = textNotice('down_change', ['url' => route('downs.view', ['id' => $down->id], false), 'title' => $down->title]);
                    $down->user->sendMessage(null, $text);
                }

                clearCache(['statLoads', 'recentDowns', 'DownFeed']);
                setFlash('success', __('loads.down_edited_success'));

                return redirect()->route('admin.downs.edit', ['id' => $down->id]);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        $categories = $down->category->getChildren();

        return view('admin/loads/edit_down', compact('categories', 'down', 'cid', 'files'));
    }

    /**
     * Удаление загрузки
     */
    public function deleteDown(int $id): RedirectResponse
    {
        $down = Down::query()->find($id);

        if (! $down) {
            abort(404, __('loads.down_not_exist'));
        }

        if (! isAdmin(User::BOSS)) {
            abort(403, __('errors.forbidden'));
        }

        if ($down->active) {
            $down->category->decrement('count_downs');
        }

        $down->delete();

        clearCache(['statLoads', 'recentDowns', 'DownFeed']);
        setFlash('success', __('loads.down_success_deleted'));

        return redirect()->route('admin.loads.load', ['id' => $down->category_id]);
    }

    /**
     * Новые публикации
     */
    public function new(): View
    {
        $downs = Down::query()
            ->active(false)
            ->orderByDesc('created_at')
            ->with('user', 'category', 'files')
            ->paginate(setting('downlist'));

        return view('admin/loads/new', compact('downs'));
    }

    /**
     * Публикация загрузки
     */
    public function publish(int $id): RedirectResponse
    {
        $down = Down::query()->find($id);

        if (! $down) {
            abort(404, __('loads.down_not_exist'));
        }

        $active = $down->active ^ 1;

        $down->update([
            'active'     => $active,
            'updated_at' => SITETIME,
            'created_at' => SITETIME,
        ]);

        if ($active) {
            $status = __('loads.down_success_published');
            $down->category->increment('count_downs');
            $text = textNotice('down_publish', ['url' => route('downs.view', ['id' => $down->id], false), 'title' => $down->title]);

            $down->user->increment('point', setting('down_point'));
            $down->user->increment('money', setting('down_money'));
        } else {
            $status = __('loads.down_success_unpublished');
            $down->category->decrement('count_downs');
            $text = textNotice('down_unpublish', ['url' => route('downs.view', ['id' => $down->id], false), 'title' => $down->title]);

            $down->user->decrement('point', setting('down_point'));
            $down->user->decrement('money', setting('down_money'));
        }

        $down->user->sendMessage(null, $text);

        clearCache(['statLoads', 'recentDowns', 'DownFeed']);
        setFlash('success', $status);

        return redirect()->route('admin.downs.edit', ['id' => $down->id]);
    }
}
