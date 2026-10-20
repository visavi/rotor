<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StickerCategoryRequest;
use App\Http\Requests\Admin\StickerRequest;
use App\Models\Sticker;
use App\Models\StickersCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $category = $this->findCategory($id);

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
    public function store(StickerCategoryRequest $request): RedirectResponse
    {
        $category = StickersCategory::query()->create([
            'name' => $request->validated('name'),
        ]);

        return redirect('admin/stickers/' . $category->id)
            ->with('success', __('stickers.category_success_created'));
    }

    /**
     * Форма редактирования категории
     */
    public function edit(int $id): View
    {
        $category = $this->findCategory($id);

        return view('admin/stickers/edit_category', compact('category'));
    }

    /**
     * Редактирование категории
     */
    public function update(int $id, StickerCategoryRequest $request): RedirectResponse
    {
        $this->findCategory($id)->update([
            'name' => $request->validated('name'),
        ]);

        return redirect('admin/stickers')
            ->with('success', __('stickers.category_success_changed'));
    }

    /**
     * Удаление категории
     */
    public function delete(int $id): RedirectResponse
    {
        $category = $this->findCategory($id);

        if (Sticker::query()->where('category_id', $category->id)->exists()) {
            return redirect('admin/stickers')
                ->withErrors(__('stickers.category_has_stickers'));
        }

        $category->delete();

        return redirect('admin/stickers')
            ->with('success', __('stickers.category_success_deleted'));
    }

    /**
     * Форма добавления стикера
     */
    public function createSticker(Request $request): View
    {
        $cid = int($request->input('cid'));

        $categories = StickersCategory::query()->get();

        if ($categories->isEmpty()) {
            abort(200, __('stickers.empty_categories'));
        }

        $this->checkUploadDirectory();

        return view('admin/stickers/create_sticker', compact('categories', 'cid'));
    }

    /**
     * Добавление стикера
     */
    public function storeSticker(StickerRequest $request): RedirectResponse
    {
        $this->checkUploadDirectory();

        $cid = (int) $request->validated('cid');
        $file = $request->file('sticker');
        $uploadPath = (new Sticker())->uploadPath;

        $newName = uniqueName($file->getClientOriginalExtension());
        $file->move(public_path($uploadPath), $newName);

        Sticker::query()->create([
            'category_id' => $cid,
            'name'        => $uploadPath . '/' . $newName,
            'code'        => $request->validated('code'),
        ]);

        clearCache(['stickers', 'stickers_map']);

        return redirect('admin/stickers/' . $cid)
            ->with('success', __('stickers.sticker_success_created'));
    }

    /**
     * Форма редактирования стикера
     */
    public function editSticker(int $id, Request $request): View
    {
        $sticker = $this->findSticker($id);
        $page = int($request->input('page', 1));
        $categories = StickersCategory::query()->get();

        return view('admin/stickers/edit_sticker', compact('sticker', 'categories', 'page'));
    }

    /**
     * Редактирование стикера
     */
    public function updateSticker(int $id, StickerRequest $request): RedirectResponse
    {
        $cid = (int) $request->validated('cid');
        $page = int($request->input('page', 1));

        $this->findSticker($id)->update([
            'code'        => $request->validated('code'),
            'category_id' => $cid,
        ]);

        clearCache(['stickers', 'stickers_map']);

        return redirect('admin/stickers/' . $cid . '?page=' . $page)
            ->with('success', __('stickers.sticker_success_changed'));
    }

    /**
     * Удаление стикера
     */
    public function deleteSticker(int $id, Request $request): RedirectResponse
    {
        $this->checkUploadDirectory();

        $sticker = $this->findSticker($id);
        $page = int($request->input('page', 1));

        deleteFile(public_path($sticker->name));
        $sticker->delete();

        clearCache(['stickers', 'stickers_map']);

        return redirect('admin/stickers/' . $sticker->category_id . '?page=' . $page)
            ->with('success', __('stickers.sticker_success_deleted'));
    }

    /**
     * Возвращает категорию или 404
     */
    private function findCategory(int $id): StickersCategory
    {
        $category = StickersCategory::query()->find($id);

        if (! $category) {
            abort(404, __('stickers.category_not_exist'));
        }

        return $category;
    }

    /**
     * Возвращает стикер или 404
     */
    private function findSticker(int $id): Sticker
    {
        $sticker = Sticker::query()->find($id);

        if (! $sticker) {
            abort(404, __('stickers.sticker_not_exist'));
        }

        return $sticker;
    }

    /**
     * Останавливает работу, если в папку стикеров нельзя писать
     */
    private function checkUploadDirectory(): void
    {
        if (! is_writable(public_path('uploads/stickers'))) {
            abort(200, __('main.directory_not_writable'));
        }
    }
}
