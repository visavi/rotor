<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Classes\Validator;
use App\Models\Board;
use App\Models\Item;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BoardController extends AdminController
{
    /**
     * Главная страница
     *
     * @param int|null $id
     *
     * @return View
     */
    public function index(int $id = null): View
    {
        $board = null;

        if ($id) {
            /** @var Board $board */
            $board = Board::query()->find($id);

            if (! $board) {
                abort(404, __('boards.category_not_exist'));
            }
        }

        $items = Item::query()
            ->when($board, static function (Builder $query) use ($board) {
                return $query->where('board_id', $board->id);
            })
            ->where('expires_at', '>', SITETIME)
            ->orderByDesc('updated_at')
            ->with('category', 'user', 'files')
            ->paginate(Item::BOARD_PAGINATE);

        $boards = Board::query()
            ->where('parent_id', $board->id ?? 0)
            ->get();

        return view('admin/boards/index', compact('items', 'board', 'boards'));
    }

    /**
     * Категории объявлений
     *
     * @return View
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
            ->length($name, 3, 50, ['name' => __('validator.text')]);

        if ($validator->isValid()) {
            $max = Board::query()->max('sort') + 1;

            /** @var Board $board */
            $board = Board::query()->create([
                'name'  => $name,
                'sort'  => $max,
            ]);

            setFlash('success', __('boards.category_success_created'));

            return redirect('admin/boards/edit/' . $board->id);
        }

        setInput($request->all());
        setFlash('danger', $validator->getErrors());

        return redirect('admin/boards/categories');
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

        /** @var Board $board */
        $board = Board::query()->with('children')->find($id);

        if (! $board) {
            abort(404, __('boards.category_not_exist'));
        }

        $boards = Board::query()
            ->where('parent_id', 0)
            ->orderBy('sort')
            ->get();

        if ($request->isMethod('post')) {
            $parent = int($request->input('parent'));
            $name   = $request->input('name');
            $sort   = int($request->input('sort'));
            $closed = empty($request->input('closed')) ? 0 : 1;

            $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->length($name, 3, 50, ['name' => __('validator.text')])
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

                return redirect('admin/boards/categories');
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return view('admin/boards/edit', compact('boards', 'board'));
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

        /** @var Board $board */
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

        return redirect('admin/boards/categories');
    }

    /**
     * Редактирование объявления
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     *
     * @return View|RedirectResponse
     */
    public function editItem(int $id, Request $request, Validator $validator)
    {
        /** @var Item $item */
        $item = Item::query()->find($id);

        if (! $item) {
            abort(404, __('boards.item_not_exist'));
        }

        if ($request->isMethod('post')) {
            $bid   = int($request->input('bid'));
            $title = $request->input('title');
            $text  = $request->input('text');
            $price = int($request->input('price'));
            $phone = preg_replace('/\D/', '', $request->input('phone') ?? '');

            /** @var Board $board */
            $board = Board::query()->find($bid);

            $validator
                ->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->length($title, 3, 50, ['title' => __('validator.text')])
                ->length($text, 50, 5000, ['text' => __('validator.text')])
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

                clearCache(['statBoards', 'recentBoards', 'statWidget']);
                setFlash('success', __('boards.item_success_edited'));

                return redirect('admin/items/edit/' . $item->id);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        $boards = Board::query()
            ->where('parent_id', 0)
            ->with('children')
            ->orderBy('sort')
            ->get();

        return view('/admin/boards/edit_item', compact('item', 'boards'));
    }

    /**
     * Удаление объявления
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     *
     * @return RedirectResponse
     * @throws Exception
     */
    public function deleteItem(int $id, Request $request, Validator $validator): RedirectResponse
    {
        /** @var Item $item */
        $item = Item::query()->find($id);

        if (! $item) {
            abort(404, __('boards.item_not_exist'));
        }

        $validator->equal($request->input('_token'), csrf_token(), __('validator.token'));

        if ($validator->isValid()) {
            $item->delete();
            $item->category->decrement('count_items');

            clearCache(['statBoards', 'recentBoards', 'statWidget']);
            setFlash('success', __('boards.item_success_deleted'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        return redirect('admin/boards/' . $item->board_id);
    }

    /**
     * Пересчет голосов
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
            restatement('boards');

            setFlash('success', __('main.success_recounted'));
        } else {
            setFlash('danger', __('validator.token'));
        }

        return redirect('admin/boards');
    }
}
