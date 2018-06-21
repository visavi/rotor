<?php

namespace App\Controllers;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\Board;
use App\Models\Flood;
use App\Models\Item;

class BoardController extends BaseController
{
    /**
     * Главная страница
     */
    public function index()
    {
        $total = Item::query()->count();
        $page = paginate(10, $total);

        $items = Item::query()
            ->orderBy('created_at', 'desc')
            ->limit($page->limit)
            ->offset($page->offset)
            ->with('category.parent', 'user', 'files')
            ->get();

        return view('boards/index', compact('items', 'page'));
    }

    /**
     * Просмотр объявления
     */
    public function view($id)
    {
        $item = Item::query()
            ->where('expires_at', '>', SITETIME)
            ->with('category')
            ->find($id);

        if (! $item) {
            abort(404, 'Объявление не найдено!');
        }

        return view('boards/view', compact('item'));
    }

    /**
     * Создание объявления
     */
    public function create()
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
            $files = (array) Request::file('files');

            $board = Board::query()->find($bid);

            $validator = new Validator();
            $validator
                ->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->length($title, 5, 50, ['title' => 'Слишком длинное или короткое название!'])
                ->length($text, 50, 5000, ['text' => 'Слишком длинный или короткий текст описания!'])
                ->true(Flood::isFlood(), ['text' => 'Антифлуд! Разрешается добавлять объявления раз в ' . Flood::getPeriod() . ' секунд!'])
                ->notEmpty($board, ['category' => 'Категории для данного объявления не существует!']);

            if ($board) {
                $validator->empty($board->closed, ['category' => 'В данный раздел запрещено добавлять объявления!']);
            }

            $validator->lte(count($files), setting('maxfiles'), ['files' => 'Разрешено загружать не более ' . setting('maxfiles') . ' файлов']);

            if ($validator->isValid()) {
                $rules = [
                    'maxsize'    => setting('fileupload'),
                    'extensions' => explode(',', setting('allowextload')),
                    'minweight'  => 100,
                ];

                foreach ($files as $file) {
                    $validator->file($file, $rules, ['files' => 'Не удалось загрузить файл!']);
                }
            }

            if ($validator->isValid()) {

                $item = Item::query()->create([
                    'board_id'   => $board->id,
                    'title'      => $title,
                    'text'       => $text,
                    'user_id'    => $user->id,
                    'price'      => $price,
                    'created_at' => SITETIME,
                    'expires_at' => strtotime('+1 month', SITETIME),
                ]);

                foreach ($files as $file) {
                    $item->uploadFile($file);
                }

                setFlash('success', 'Объявления успешно добавлено!');
                redirect('/items/' . $item->id);
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('boards/create', compact('boards', 'bid'));
    }
}
