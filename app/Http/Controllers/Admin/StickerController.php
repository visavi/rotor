<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Classes\Validator;
use App\Models\Sticker;
use App\Models\StickersCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StickerController extends AdminController
{
    /**
     * Главная страница
     */
    public function index(): View
    {
        $categories = StickersCategory::query()
            ->selectRaw('sc.id, sc.name, count(s.id) cnt')
            ->from('stickers_categories as sc')
            ->leftJoin('stickers as s', 's.category_id', 'sc.id')
            ->groupBy('sc.id')
            ->orderBy('sc.id')
            ->get();

        return view('admin/stickers/index', compact('categories'));
    }

    /**
     * Просмотр стикеров по категориям
     */
    public function category(int $id): View
    {
        $category = StickersCategory::query()->where('id', $id)->first();

        if (! $category) {
            abort(404, __('stickers.category_not_exist'));
        }

        $stickers = Sticker::query()
            ->where('category_id', $id)
            ->orderBy(DB::raw('CHAR_LENGTH(code)'))
            ->orderBy('name')
            ->with('category')
            ->paginate(setting('stickerlist'));

        return view('admin/stickers/category', compact('category', 'stickers'));
    }

    /**
     * Создание категории
     */
    public function create(Request $request, Validator $validator): RedirectResponse
    {
        $name = $request->input('name');

        $validator->length($name, 3, 50, ['name' => __('validator.text')]);

        if ($validator->isValid()) {
            $category = StickersCategory::query()->create([
                'name'       => $name,
                'created_at' => SITETIME,
            ]);

            setFlash('success', __('stickers.category_success_created'));

            return redirect('admin/stickers/' . $category->id);
        }

        setInput($request->all());
        setFlash('danger', $validator->getErrors());

        return redirect('admin/stickers');
    }

    /**
     * Редактирование категории
     */
    public function edit(int $id, Request $request, Validator $validator): View|RedirectResponse
    {
        $category = StickersCategory::query()->find($id);

        if (! $category) {
            abort(404, __('stickers.category_not_exist'));
        }

        if ($request->isMethod('post')) {
            $name = $request->input('name');

            $validator->length($name, 3, 50, ['name' => __('validator.text')]);

            if ($validator->isValid()) {
                $category->update([
                    'name'       => $name,
                    'updated_at' => SITETIME,
                ]);

                setFlash('success', __('stickers.category_success_changed'));

                return redirect('admin/stickers');
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return view('admin/stickers/edit_category', compact('category'));
    }

    /**
     * Удаление категории
     */
    public function delete(int $id, Request $request, Validator $validator): RedirectResponse
    {
        $category = StickersCategory::query()->find($id);

        if (! $category) {
            abort(404, __('stickers.category_not_exist'));
        }

        $sticker = Sticker::query()->where('category_id', $category->id)->first();
        if ($sticker) {
            $validator->addError(__('stickers.category_has_stickers'));
        }

        if ($validator->isValid()) {
            $category->delete();

            setFlash('success', __('stickers.category_success_deleted'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        return redirect('admin/stickers');
    }

    /**
     * Добавление стикера
     */
    public function createSticker(Request $request, Validator $validator): View|RedirectResponse
    {
        $cid = int($request->input('cid'));

        $categories = StickersCategory::query()->get();

        if ($categories->isEmpty()) {
            abort(200, __('stickers.empty_categories'));
        }

        if (! is_writable(public_path('uploads/stickers'))) {
            abort(200, __('main.directory_not_writable'));
        }

        if ($request->isMethod('post')) {
            $code = Str::lower((string) $request->input('code'));
            $sticker = $request->file('sticker');

            $validator
                ->length($code, 2, 20, ['code' => __('stickers.sticker_length')])
                ->regex($code, '|^:[\p{L}\p{N}_\-]+$|iu', ['code' => __('stickers.sticker_requirements')]);

            $category = StickersCategory::query()->where('id', $cid)->first();
            $validator->notEmpty($category, ['category' => __('stickers.category_not_exist')]);

            $duplicate = Sticker::query()->where('code', $code)->first();
            $validator->empty($duplicate, ['code' => __('stickers.sticker_exists')]);

            $rules = [
                'maxsize'    => setting('stickermaxsize'),
                'maxweight'  => setting('stickermaxweight'),
                'minweight'  => setting('stickerminweight'),
                'extensions' => explode(',', setting('image_extensions')),
            ];

            $validator->file($sticker, $rules, ['sticker' => __('validator.image_upload_failed')]);

            if ($validator->isValid()) {
                $newName = uniqueName($sticker->getClientOriginalExtension());
                $path = (new Sticker())->uploadPath . '/' . $newName;
                $sticker->move(public_path((new Sticker())->uploadPath), $newName);

                Sticker::query()->create([
                    'category_id' => $cid,
                    'name'        => $path,
                    'code'        => $code,
                ]);

                clearCache('stickers');
                setFlash('success', __('stickers.sticker_success_created'));

                return redirect('admin/stickers/' . $cid);
            }

            return redirect('/admin/stickers/sticker/create')
                ->withErrors($validator->getErrors())
                ->withInput();
        }

        return view('admin/stickers/create_sticker', compact('categories', 'cid'));
    }

    /**
     * Редактирование стикера
     */
    public function editSticker(int $id, Request $request, Validator $validator): View|RedirectResponse
    {
        $sticker = Sticker::query()->find($id);
        $page = int($request->input('page', 1));

        if (! $sticker) {
            abort(404, __('stickers.sticker_not_exist'));
        }

        if ($request->isMethod('post')) {
            $code = Str::lower((string) $request->input('code'));
            $cid = int($request->input('cid'));

            $validator
                ->length($code, 2, 20, ['code' => __('stickers.sticker_length')])
                ->regex($code, '|^:[\p{L}\p{N}_\-]+$|iu', ['code' => __('stickers.sticker_requirements')]);

            $duplicate = Sticker::query()->where('code', $code)->where('id', '<>', $sticker->id)->first();
            $validator->empty($duplicate, ['code' => __('stickers.sticker_exists')]);

            $category = StickersCategory::query()->where('id', $cid)->first();
            $validator->notEmpty($category, ['category' => __('stickers.category_not_exist')]);

            if ($validator->isValid()) {
                $sticker->update([
                    'code'        => $code,
                    'category_id' => $cid,
                ]);

                clearCache('stickers');
                setFlash('success', __('stickers.sticker_success_changed'));

                return redirect('admin/stickers/' . $cid . '?page=' . $page);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        $categories = StickersCategory::query()->get();

        return view('admin/stickers/edit_sticker', compact('sticker', 'categories', 'page'));
    }

    /**
     * Удаление стикера
     s*/
    public function deleteSticker(int $id, Request $request): RedirectResponse
    {
        if (! is_writable(public_path('uploads/stickers'))) {
            abort(200, __('main.directory_not_writable'));
        }

        $sticker = Sticker::query()->where('id', $id)->first();

        if (! $sticker) {
            abort(404, __('stickers.sticker_not_exist'));
        }

        $page = int($request->input('page', 1));
        $category = $sticker->category->id;

        deleteFile(public_path($sticker->name));
        $sticker->delete();

        clearCache('stickers');
        setFlash('success', __('stickers.sticker_success_deleted'));

        return redirect('admin/stickers/' . $category . '?page=' . $page);
    }
}
