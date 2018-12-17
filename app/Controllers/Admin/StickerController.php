<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Classes\Validator;
use App\Models\Sticker;
use App\Models\StickersCategory;
use App\Models\User;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Http\Request;

class StickerController extends AdminController
{
    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();

        if (! isAdmin(User::ADMIN)) {
            abort(403, 'Доступ запрещен!');
        }
    }

    /**
     * Главная страница
     *
     * @return string
     */
    public function index(): string
    {
        $categories = StickersCategory::query()->get();

        if ($categories->isNotEmpty()) {
            return view('admin/stickers/index', compact('categories'));
        }

        return $this->category(0);
    }

    /**
     * Просмотр стикеров по категориям
     *
     * @param int $id
     * @return string
     */
    public function category(int $id): string
    {
        $category = null;

        if ($id) {
            $category = StickersCategory::query()->where('id', $id)->first();

            if (! $category) {
                abort(404, 'Данной категории не существует!');
            }
        }

        $total = Sticker::query()->where('category_id', $id)->count();
        $page = paginate(setting('stickerlist'), $total);

        $stickers = Sticker::query()
            ->where('category_id', $id)
            ->orderBy(DB::connection()->raw('CHAR_LENGTH(`code`)'))
            ->orderBy('name')
            ->limit($page->limit)
            ->offset($page->offset)
            ->with('category')
            ->get();

        return view('admin/stickers/category', compact('category', 'stickers', 'page'));
    }

    /**
     * Добавление стикера
     *
     * @param Request   $request
     * @param Validator $validator
     * @return string
     */
    public function create(Request $request, Validator $validator): string
    {
        $cid  = int($request->input('cid'));

        if (! is_writable(UPLOADS.'/stickers')){
            abort('default', 'Директория со стикерами недоступна для записи!');
        }

        if ($request->isMethod('post')) {
            $token   = check($request->input('token'));
            $code    = check(utfLower($request->input('code')));
            $sticker = $request->file('sticker');

            $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->length($code, 2, 20, ['code' => 'Слишком длинный или короткий код стикера!'])
                ->regex($code, '|^:+[a-яa-z0-9_\-/\(\)]+$|i', ['code' => 'Код стикера должен начинаться с двоеточия. Разрешены буквы, цифры и дефис!']);

            if ($cid) {
                $category = StickersCategory::query()->where('id', $cid)->first();
                $validator->notEmpty($category, ['category' => 'Данной категории не существует!']);
            }

            $duplicate = Sticker::query()->where('code', $code)->first();
            $validator->empty($duplicate, ['code' => 'Стикер с данным кодом уже имеется в списке!']);

            $rules = [
                'maxsize'   => setting('stickermaxsize'),
                'maxweight' => setting('stickermaxweight'),
                'minweight' => setting('stickerminweight'),
            ];

            $validator->file($sticker, $rules, ['sticker' => 'Не удалось загрузить изображение!']);

            if ($validator->isValid()) {

                $newName = uniqueName($sticker->getClientOriginalExtension());
                $path    = (new Sticker())->uploadPath . '/' . $newName;
                $sticker->move((new Sticker())->uploadPath, $newName);

                Sticker::query()->create([
                    'category_id' => $cid,
                    'name'        => str_replace(HOME, '', $path),
                    'code'        => $code,
                ]);

                clearCache('stickers');

                setFlash('success', 'Стикер успешно загружен!');
                redirect('/admin/stickers/' . $cid);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $categories = StickersCategory::query()->get();

        return view('admin/stickers/create', compact('categories', 'cid'));
    }

    /**
     * Редактирование стикера
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     * @return string
     */
    public function edit(int $id, Request $request, Validator $validator): string
    {
        /** @var Sticker $sticker */
        $sticker = Sticker::query()->find($id);
        $page    = int($request->input('page', 1));

        if (! $sticker) {
            abort(404, 'Данного стикера не существует!');
        }

        if ($request->isMethod('post')) {

            $token = check($request->input('token'));
            $code  = check(utfLower($request->input('code')));
            $cid   = int($request->input('cid'));

            $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->length($code, 2, 20, ['code' => 'Слишком длинный или короткий код стикера!'])
                ->regex($code, '|^:+[a-яa-z0-9_\-/\(\)]+$|i', ['code' => 'Код стикера должен начинаться с двоеточия. Разрешены буквы, цифры и дефис!']);

            $duplicate = Sticker::query()->where('code', $code)->where('id', '<>', $sticker->id)->first();
            $validator->empty($duplicate, ['code' => 'Стикер с данным кодом уже имеется в списке!']);

            if ($cid) {
                $category = StickersCategory::query()->where('id', $cid)->first();
                $validator->notEmpty($category, ['category' => 'Данной категории не существует!']);
            }

            if ($validator->isValid()) {

                $sticker->update([
                    'code'        => $code,
                    'category_id' => $cid,
                ]);

                clearCache('stickers');

                setFlash('success', 'Стикер успешно отредактирован!');
                redirect('/admin/stickers/' . $cid . '?page=' . $page);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $categories = StickersCategory::query()->get();

        return view('admin/stickers/edit', compact('sticker', 'categories', 'page'));
    }

    /**
     * Удаление стикера
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     * @return void
     * @throws \Exception
     */
    public function delete(int $id, Request $request, Validator $validator): void
    {
        if (! is_writable(UPLOADS . '/stickers')){
            abort('default', 'Директория со стикерами недоступна для записи!');
        }

        $sticker = Sticker::query()->where('id', $id)->first();

        if (! $sticker) {
            abort(404, 'Данного стикреа не существует!');
        }

        $page     = int($request->input('page', 1));
        $token    = check($request->input('token'));
        $category = (int) $sticker->category->id;

        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!');

        if ($validator->isValid()) {

            deleteFile(HOME . $sticker->name, false);
            $sticker->delete();

            clearCache('stickers');

            setFlash('success', 'Стикер успешно удален!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/stickers/' . $category . '?page=' . $page);
    }
}
