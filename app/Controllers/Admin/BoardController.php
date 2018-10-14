<?php

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
                abort(404, 'Категория не найдена!');
            }
        }

        $total = Item::query()
            ->when($board, function (Builder $query) use ($board) {
                return $query->where('board_id', $board->id);
            })
            ->where('expires_at', '>', SITETIME)
            ->count();

        $page = paginate(10, $total);

        $items = Item::query()
            ->when($board, function (Builder $query) use ($board) {
                return $query->where('board_id', $board->id);
            })
            ->where('expires_at', '>', SITETIME)
            ->orderBy('updated_at', 'desc')
            ->limit($page->limit)
            ->offset($page->offset)
            ->with('category', 'user', 'files')
            ->get();

        $boards = Board::query()
            ->where('parent_id', $board->id ?? 0)
            ->get();

        return view('admin/boards/index', compact('items', 'page', 'board', 'boards'));
    }

    /**
     * Категории объявлений
     *
     * @return string
     */
    public function categories(): string
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, 'Доступ запрещен!');
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
            abort(403, 'Доступ запрещен!');
        }

        $token = check($request->input('token'));
        $name  = check($request->input('name'));

        $validator->equal($token, $_SESSION['token'], trans('validator.token'))
            ->length($name, 3, 50, ['name' => 'Слишком длинное или короткое название раздела!']);

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
            abort(403, 'Доступ запрещен!');
        }

        /** @var Board $board */
        $board = Board::query()->with('children')->find($id);

        if (! $board) {
            abort(404, 'Данного раздела не существует!');
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

            $validator->equal($token, $_SESSION['token'], trans('validator.token'))
                ->length($name, 3, 50, ['title' => 'Слишком длинное или короткое название раздела!'])
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
            abort(403, 'Доступ запрещен!');
        }

        /** @var Board $board */
        $board = Board::query()->with('children')->find($id);

        if (! $board) {
            abort(404, 'Данного раздела не существует!');
        }

        $token = check($request->input('token'));

        $validator->equal($token, $_SESSION['token'], trans('validator.token'))
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
            abort(404, 'Данного объявления не существует!');
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
                ->equal($token, $_SESSION['token'], trans('validator.token'))
                ->length($title, 5, 50, ['title' => trans('validator.name')])
                ->length($text, 50, 5000, ['text' => trans('validator.text')])
                ->regex($phone, '#^\d{11}$#', ['phone' => trans('validator.phone')], false)
                ->notEmpty($board, ['bid' => 'Категории для данного объявления не существует!']);

            if ($board) {
                $validator->empty($board->closed, ['bid' => 'В данный раздел запрещено добавлять объявления!']);
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

                setFlash('success', 'Объявление успешно отредактировано!');
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
            abort(404, 'Данного объявления не существует!');
        }

        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!');

        if ($validator->isValid()) {

            $item->delete();

            $item->category->decrement('count_items');

            setFlash('success', 'Объявление успешно удалено!');
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
            abort(403, 'Доступ запрещен!');
        }

        $token = check($request->input('token'));

        if ($token === $_SESSION['token']) {

            restatement('boards');

            setFlash('success', 'Объявления успешно пересчитаны!');
        } else {
            setFlash('danger', 'Ошибка! Неверный идентификатор сессии, повторите действие!');
        }

        redirect('/admin/boards');
    }
}
