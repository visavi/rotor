<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Classes\Validator;
use App\Models\Board;
use App\Models\Item;
use App\Models\User;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;

class BoardController extends AdminController
{
    /**
     * Главная страница
     *
     * @param int $id
     * @return string
     */
    public function index($id = null): string
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
     * @return string
     */
    public function categories(): string
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
            ->length($name, 3, 50, ['name' => __('validator.text')]);

        if ($validator->isValid()) {

            $max = Board::query()->max('sort') + 1;

            /** @var Board $board */
            $board = Board::query()->create([
                'name'  => $name,
                'sort'  => $max,
            ]);

            setFlash('success', 'Новый раздел успешно создан!');
            redirect('/admin/boards/edit/' . $board->id);
        } else {
            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/boards/categories');
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
            $token  = check($request->input('token'));
            $parent = int($request->input('parent'));
            $name   = check($request->input('name'));
            $sort   = check($request->input('sort'));
            $closed = empty($request->input('closed')) ? 0 : 1;

            $validator->equal($token, $_SESSION['token'], __('validator.token'))
                ->length($name, 3, 50, ['name' => __('validator.text')])
                ->notEqual($parent, $board->id, ['parent' => 'Недопустимый выбор родительского раздела!']);

            if (! empty($parent) && $board->children->isNotEmpty()) {
                $validator->addError(['parent' => 'Текущий раздел имеет подразделы!']);
            }

            if ($validator->isValid()) {

                $board->update([
                    'parent_id' => $parent,
                    'name'      => $name,
                    'sort'      => $sort,
                    'closed'    => $closed,
                ]);

                setFlash('success', 'Раздел успешно отредактирован!');
                redirect('/admin/boards/categories');
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/boards/edit', compact('boards', 'board'));
    }

    /**
     * Удаление раздела
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     * @throws Exception
     */
    public function delete(int $id, Request $request, Validator $validator): void
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, __('errors.forbidden'));
        }

        /** @var Board $board */
        $board = Board::query()->with('children')->find($id);

        if (! $board) {
            abort(404, __('boards.category_not_exist'));
        }

        $token = check($request->input('token'));

        $validator->equal($token, $_SESSION['token'], __('validator.token'))
            ->true($board->children->isEmpty(), 'Удаление невозможно! Данный раздел имеет подразделы!');

        $item = Item::query()->where('board_id', $board->id)->first();
        if ($item) {
            $validator->addError('Удаление невозможно! В данном разделе имеются объявления!');
        }

        if ($validator->isValid()) {

            $board->delete();

            setFlash('success', 'Раздел успешно удален!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/boards/categories');
    }

    /**
     * Редактирование объявления
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     * @return string
     */
    public function editItem(int $id, Request $request, Validator $validator): string
    {
        /** @var Item $item */
        $item = Item::query()->find($id);

        if (! $item) {
            abort(404, __('boards.item_not_exist'));
        }

        if ($request->isMethod('post')) {
            $token = check($request->input('token'));
            $bid   = int($request->input('bid'));
            $title = check($request->input('title'));
            $text  = check($request->input('text'));
            $price = check($request->input('price'));
            $phone = preg_replace('/\D/', '', $request->input('phone'));

            /** @var Board $board */
            $board = Board::query()->find($bid);

            $validator
                ->equal($token, $_SESSION['token'], __('validator.token'))
                ->length($title, 5, 50, ['title' => __('validator.text')])
                ->length($text, 50, 5000, ['text' => __('validator.text')])
                ->regex($phone, '#^\d{11}$#', ['phone' => __('validator.phone')], false)
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

                clearCache(['statBoards', 'recentBoards']);
                setFlash('success', __('boards.item_success_edited'));
                redirect('/admin/items/edit/' . $item->id);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
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
     * @throws Exception
     */
    public function deleteItem(int $id, Request $request, Validator $validator): void
    {
        $token = check($request->input('token'));

        /** @var Item $item */
        $item = Item::query()->find($id);

        if (! $item) {
            abort(404, __('boards.item_not_exist'));
        }

        $validator->equal($token, $_SESSION['token'], __('validator.token'));

        if ($validator->isValid()) {

            $item->delete();

            $item->category->decrement('count_items');

            clearCache(['statBoards', 'recentBoards']);
            setFlash('success', __('boards.item_success_deleted'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/boards/' . $item->board_id);
    }

    /**
     * Пересчет голосов
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
            restatement('boards');

            setFlash('success', 'Объявления успешно пересчитаны!');
        } else {
            setFlash('danger', __('validator.token'));
        }

        redirect('/admin/boards');
    }
}
