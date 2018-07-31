<?php

namespace App\Controllers\Admin;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\Board;
use App\Models\Item;
use App\Models\User;
use Exception;
use Illuminate\Database\Capsule\Manager as DB;

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
            $board = Board::query()->find($id);

            if (! $board) {
                abort(404, 'Категория не найдена!');
            }
        }

        $total = Item::query()
            ->when($board, function ($query) use ($board) {
                return $query->where('board_id', $board->id);
            })
            ->where('expires_at', '>', SITETIME)
            ->count();

        $page = paginate(10, $total);

        $items = Item::query()
            ->when($board, function ($query) use ($board) {
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
     * @return void
     */
    public function create(): void
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, 'Доступ запрещен!');
        }

        $token = check(Request::input('token'));
        $name  = check(Request::input('name'));

        $validator = new Validator();
        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
            ->length($name, 3, 50, ['name' => 'Слишком длинное или короткое название раздела!']);

        if ($validator->isValid()) {

            $max = Board::query()->max('sort') + 1;

            $board = Board::query()->create([
                'name'  => $name,
                'sort'  => $max,
            ]);

            setFlash('success', 'Новый раздел успешно создан!');
            redirect('/admin/boards/edit/' . $board->id);
        } else {
            setInput(Request::all());
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/boards/categories');
    }

    /**
     * Редактирование раздела
     *
     * @param int $id
     * @return string
     */
    public function edit($id): string
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, 'Доступ запрещен!');
        }

        $board = Board::query()->with('children')->find($id);

        if (! $board) {
            abort(404, 'Данного раздела не существует!');
        }

        $boards = Board::query()
            ->where('parent_id', 0)
            ->orderBy('sort')
            ->get();

        if (Request::isMethod('post')) {
            $token  = check(Request::input('token'));
            $parent = int(Request::input('parent'));
            $name   = check(Request::input('name'));
            $sort   = check(Request::input('sort'));
            $closed = empty(Request::input('closed')) ? 0 : 1;

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
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
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/boards/edit', compact('boards', 'board'));
    }

    /**
     * Удаление раздела
     *
     * @param int $id
     * @throws Exception
     */
    public function delete($id): void
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, 'Доступ запрещен!');
        }

        $board = Board::query()->with('children')->find($id);

        if (! $board) {
            abort(404, 'Данного раздела не существует!');
        }

        $token = check(Request::input('token'));

        $validator = new Validator();
        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
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
     * @param int $id
     * @return string
     */
    public function editItem($id): string
    {
        $item = Item::query()->find($id);

        if (! $item) {
            abort(404, 'Данного объявления не существует!');
        }

        if (Request::isMethod('post')) {
            $token = check(Request::input('token'));
            $bid   = int(Request::input('bid'));
            $title = check(Request::input('title'));
            $text  = check(Request::input('text'));
            $price = check(Request::input('price'));
            $phone = preg_replace('/\D/', '', Request::input('phone'));

            $board = Board::query()->find($bid);

            $validator = new Validator();
            $validator
                ->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->length($title, 5, 50, ['title' => 'Слишком длинное или короткое название!'])
                ->length($text, 50, 5000, ['text' => 'Слишком длинный или короткий текст описания!'])
                ->regex($phone, '#^\d{11}$#', ['phone' => 'Недопустимый формат телефона. Пример: 8-900-123-45-67!'], false)
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
                setInput(Request::all());
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
     * @param int $id
     * @throws Exception
     */
    public function deleteItem($id): void
    {
        $token = check(Request::input('token'));

        $item = Item::query()->find($id);

        if (! $item) {
            abort(404, 'Данного объявления не существует!');
        }

        $validator = new Validator();
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
     * @return void
     */
    public function restatement(): void
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, 'Доступ запрещен!');
        }

        $token = check(Request::input('token'));

        if ($token === $_SESSION['token']) {

            restatement('boards');

            setFlash('success', 'Объявления успешно пересчитаны!');
        } else {
            setFlash('danger', 'Ошибка! Неверный идентификатор сессии, повторите действие!');
        }

        redirect('/admin/boards');
    }
}
