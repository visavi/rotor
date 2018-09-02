<?php

namespace App\Controllers;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\Board;
use App\Models\File;
use App\Models\Flood;
use App\Models\Item;
use Exception;

class BoardController extends BaseController
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

        return view('boards/index', compact('items', 'page', 'board', 'boards'));
    }

    /**
     * Просмотр объявления
     *
     * @param int $id
     * @return string
     */
    public function view($id): string
    {
        $item = Item::query()
            ->with('category')
            ->find($id);

        if (! $item) {
            abort(404, 'Объявление не найдено!');
        }

        if ($item->expires_at <= SITETIME && $item->user_id !== getUser('id')) {
            abort('default', 'Объявление не активно или истек срок размещения!');
        }

        return view('boards/view', compact('item'));
    }

    /**
     * Создание объявления
     *
     * @return string
     */
    public function create(): string
    {
        $bid = int(Request::input('bid'));

        if (! $user = getUser()) {
            abort(403);
        }

        $boards = Board::query()
            ->where('parent_id', 0)
            ->with('children')
            ->orderBy('sort')
            ->get();

        if ($boards->isEmpty()) {
            abort('default', 'Разделы объявлений еще не созданы!');
        }

        if (Request::isMethod('post')) {

            $token = check(Request::input('token'));
            $title = check(Request::input('title'));
            $text  = check(Request::input('text'));
            $price = int(Request::input('price'));
            $phone = preg_replace('/\D/', '', Request::input('phone'));

            $board = Board::query()->find($bid);

            $validator = new Validator();
            $validator
                ->equal($token, $_SESSION['token'], trans('validator.token'))
                ->length($title, 5, 50, ['title' => trans('validator.name')])
                ->length($text, 50, 5000, ['text' => trans('validator.text')])
                ->regex($phone, '#^\d{11}$#', ['phone' => trans('validator.phone')], false)
                ->true(Flood::isFlood(), ['text' =>  trans('validator.flood', ['sec' => Flood::getPeriod()])])
                ->notEmpty($board, ['category' => 'Категории для данного объявления не существует!']);

            if ($board) {
                $validator->empty($board->closed, ['category' => 'В данный раздел запрещено добавлять объявления!']);
            }

            if ($validator->isValid()) {

                $item = Item::query()->create([
                    'board_id'   => $board->id,
                    'title'      => $title,
                    'text'       => $text,
                    'user_id'    => $user->id,
                    'price'      => $price,
                    'phone'      => $phone,
                    'created_at' => SITETIME,
                    'updated_at' => SITETIME,
                    'expires_at' => strtotime('+1 month', SITETIME),
                ]);

                $item->category->increment('count_items');

                File::query()
                    ->where('relate_type', Item::class)
                    ->where('relate_id', 0)
                    ->where('user_id', getUser('id'))
                    ->update(['relate_id' => $item->id]);

                setFlash('success', 'Объявления успешно добавлено!');
                redirect('/items/' . $item->id);
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $files = File::query()
            ->where('relate_type', Item::class)
            ->where('relate_id', 0)
            ->where('user_id', getUser('id'))
            ->get();

        return view('boards/create', compact('boards', 'bid', 'files'));
    }

    /**
     * Редактирование объявления
     *
     * @param int $id
     * @return string
     */
    public function edit($id): string
    {
        if (! getUser()) {
            abort(403, 'Для редактирования объявления необходимо авторизоваться');
        }

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
                ->equal($token, $_SESSION['token'], trans('validator.token'))
                ->length($title, 5, 50, ['title' => trans('validator.name')])
                ->length($text, 50, 5000, ['text' => trans('validator.text')])
                ->regex($phone, '#^\d{11}$#', ['phone' => trans('validator.phone')], false)
                ->notEmpty($board, ['category' => 'Категории для данного объявления не существует!'])
                ->equal($item->user_id, getUser('id'), 'Изменение невозможно, вы не автор данного объявления!');

            if ($board) {
                $validator->empty($board->closed, ['category' => 'В данный раздел запрещено добавлять объявления!']);
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
                redirect('/items/' . $item->id);
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

        return view('boards/edit', compact('item', 'boards'));
    }

    /**
     * Снятие / Публикация объявления
     *
     * @param int $id
     */
    public function close($id): void
    {
        $token = check(Request::input('token'));

        if (! getUser()) {
            abort(403, 'Для редактирования объявления необходимо авторизоваться');
        }

        $item = Item::query()->find($id);

        if (! $item) {
            abort(404, 'Данного объявления не существует!');
        }

        $validator = new Validator();
        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
            ->equal($item->user_id, getUser('id'), 'Изменение невозможно, вы не автор данного объявления!');

        if ($validator->isValid()) {

            if ($item->expires_at > SITETIME) {
                $type = 'снято с публикации';
                $item->update([
                    'expires_at' => SITETIME,
                ]);

                $item->category->decrement('count_items');
            } else {
                $type    = 'опубликовано';
                $expired = strtotime('+1 month', $item->updated_at) <= SITETIME;

                $item->update([
                    'expires_at' => strtotime('+1 month', SITETIME),
                    'updated_at' => $expired ? SITETIME : $item->updated_at,
                ]);

                $item->category->increment('count_items');
            }

            setFlash('success', 'Объявление успешно ' . $type . '!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/items/edit/' . $item->id);
    }

    /**
     * Удаление объявления
     *
     * @param int $id
     * @throws Exception
     */
    public function delete($id): void
    {
        $token = check(Request::input('token'));

        if (! getUser()) {
            abort(403, 'Для редактирования объявления необходимо авторизоваться');
        }

        $item = Item::query()->find($id);

        if (! $item) {
            abort(404, 'Данного объявления не существует!');
        }

        $validator = new Validator();
        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
            ->equal($item->user_id, getUser('id'), 'Удаление невозможно, вы не автор данного объявления!');

        if ($validator->isValid()) {

            $item->delete();

            $item->category->decrement('count_items');

            setFlash('success', 'Объявление успешно удалено!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/boards/' . $item->board_id);
    }

    /**
     * Мои объявления
     *
     * @return string
     */
    public function active(): string
    {
        if (! getUser()) {
            abort(403, 'Для просмотра своих объявлений необходимо авторизоваться');
        }

        $total = Item::query()->where('user_id', getUser('id'))->count();

        $page = paginate(10, $total);

        $items = Item::query()
            ->where('user_id', getUser('id'))
            ->orderBy('updated_at', 'desc')
            ->limit($page->limit)
            ->offset($page->offset)
            ->with('category', 'user', 'files')
            ->get();

        return view('boards/active', compact('items', 'page'));
    }
}
