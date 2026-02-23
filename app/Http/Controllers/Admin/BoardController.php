<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Classes\Validator;
use App\Models\Board;
use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BoardController extends AdminController
{
    /**
     * Главная страница
     */
    public function index(Request $request, ?int $id = null): View
    {
        $board = null;

        if ($id) {
            $board = Board::query()->find($id);

            if (! $board) {
                abort(404, __('boards.category_not_exist'));
            }
        }

        $sort = $request->input('sort', 'date');
        $order = $request->input('order', 'desc');

        [$sorting, $orderBy] = Item::getSorting($sort, $order);

        $items = Item::query()
            ->when($board, static function (Builder $query) use ($board) {
                return $query->where('board_id', $board->id);
            })
            ->where('expires_at', '>', SITETIME)
            ->orderBy(...$orderBy)
            ->with('category', 'user', 'files')
            ->paginate(setting('boards_per_page'))
            ->appends(compact('sort', 'order'));

        $boards = Board::query()
            ->where('parent_id', $board->id ?? 0)
            ->get();

        return view('admin/boards/index', compact('items', 'board', 'boards', 'sorting'));
    }

    /**
     * Категории объявлений
     */
    public function categories(): View
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, __('errors.forbidden'));
        }

        $boards = Board::query()
            ->where('parent_id', 0)
            ->orderBy('sort')
            ->with('children')
            ->get();

        return view('admin/boards/categories', compact('boards'));
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

        $validator->length($name, setting('board_category_min'), setting('board_category_max'), ['name' => __('validator.text')]);

        if ($validator->isValid()) {
            $max = Board::query()->max('sort') + 1;

            $board = Board::query()->create([
                'name' => $name,
                'sort' => $max,
            ]);

            setFlash('success', __('boards.category_success_created'));

            return redirect()->route('admin.boards.edit', ['id' => $board->id]);
        }

        setInput($request->all());
        setFlash('danger', $validator->getErrors());

        return redirect()->route('admin.boards.categories');
    }

    /**
     * Редактирование раздела
     */
    public function edit(int $id, Request $request, Validator $validator): View|RedirectResponse
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, __('errors.forbidden'));
        }

        $board = Board::query()->with('children')->find($id);

        if (! $board) {
            abort(404, __('boards.category_not_exist'));
        }

        if ($request->isMethod('post')) {
            $parent = int($request->input('parent'));
            $name = $request->input('name');
            $sort = int($request->input('sort'));
            $closed = empty($request->input('closed')) ? 0 : 1;

            $validator
                ->length($name, setting('board_category_min'), setting('board_category_max'), ['name' => __('validator.text')])
                ->notEqual($parent, $board->id, ['parent' => __('boards.category_parent_invalid')]);

            if (! empty($parent) && $board->children->isNotEmpty()) {
                $validator->addError(['parent' => __('boards.category_has_subsections')]);
            }

            if ($validator->isValid()) {
                $board->update([
                    'parent_id' => $parent,
                    'name'      => $name,
                    'sort'      => $sort,
                    'closed'    => $closed,
                ]);

                setFlash('success', __('boards.category_success_edited'));

                return redirect()->route('admin.boards.categories');
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        $boards = $board->getChildren();

        return view('admin/boards/edit', compact('boards', 'board'));
    }

    /**
     * Удаление раздела
     */
    public function delete(int $id, Request $request, Validator $validator): RedirectResponse
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, __('errors.forbidden'));
        }

        $board = Board::query()->with('children')->find($id);

        if (! $board) {
            abort(404, __('boards.category_not_exist'));
        }

        $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
            ->true($board->children->isEmpty(), __('boards.category_has_subsections'));

        $item = Item::query()->where('board_id', $board->id)->first();
        if ($item) {
            $validator->addError(__('boards.category_has_items'));
        }

        if ($validator->isValid()) {
            $board->delete();

            setFlash('success', __('boards.category_success_deleted'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        return redirect()->route('admin.boards.categories');
    }

    /**
     * Редактирование объявления
     */
    public function editItem(int $id, Request $request, Validator $validator): View|RedirectResponse
    {
        $item = Item::query()->find($id);

        if (! $item) {
            abort(404, __('boards.item_not_exist'));
        }

        if ($request->isMethod('post')) {
            $bid = int($request->input('bid'));
            $title = $request->input('title');
            $text = $request->input('text');
            $price = int($request->input('price'));
            $phone = preg_replace('/[^\d+]/', '', $request->input('phone') ?? '');

            $board = Board::query()->find($bid);

            $validator
                ->length($title, setting('board_title_min'), setting('board_title_max'), ['title' => __('validator.text')])
                ->length($text, setting('board_text_min'), setting('board_text_max'), ['text' => __('validator.text')])
                ->phone($phone, ['phone' => __('validator.phone')], false)
                ->notEmpty($board, ['bid' => __('boards.category_not_exist')]);

            if ($board) {
                $validator->empty($board->closed, ['bid' => __('boards.category_closed')]);
            }

            if ($validator->isValid()) {
                // Обновление счетчиков
                if ($item->board_id !== $board->id) {
                    $board->increment('count_items');
                    Board::query()->where('id', $item->board_id)->decrement('count_items');
                }

                $item->update([
                    'board_id' => $board->id,
                    'title'    => $title,
                    'text'     => $text,
                    'price'    => $price,
                    'phone'    => $phone,
                ]);

                clearCache(['statBoards', 'recentBoards', 'ItemFeed']);
                setFlash('success', __('boards.item_success_edited'));

                return redirect()->route('admin.items.edit', ['id' => $item->id]);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        $boards = $item->category->getChildren();

        return view('/admin/boards/edit_item', compact('item', 'boards'));
    }

    /**
     * Удаление объявления
     */
    public function deleteItem(int $id, Request $request, Validator $validator): RedirectResponse
    {
        $item = Item::query()->find($id);

        if (! $item) {
            abort(404, __('boards.item_not_exist'));
        }

        $validator->equal($request->input('_token'), csrf_token(), __('validator.token'));

        if ($validator->isValid()) {
            $item->delete();
            $item->category->decrement('count_items');

            clearCache(['statBoards', 'recentBoards', 'ItemFeed']);
            setFlash('success', __('boards.item_success_deleted'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        return redirect()->route('admin.boards.index', ['id' => $item->board_id]);
    }

    /**
     * Пересчет объявлений
     */
    public function restatement(): RedirectResponse
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, __('errors.forbidden'));
        }

        restatement('boards');

        return redirect()
            ->route('admin.boards.index')
            ->with('success', __('main.success_recounted'));
    }
}
