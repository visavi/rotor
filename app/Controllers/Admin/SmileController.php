<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Classes\Validator;
use App\Models\Smile;
use App\Models\SmilesCategory;
use App\Models\User;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Http\Request;

class SmileController extends AdminController
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
        $categories = SmilesCategory::query()->get();

        if ($categories->isNotEmpty()) {
            return view('admin/smiles/index', compact('categories'));
        }

        return $this->category(0);
    }

    /**
     * Просмотр смайлов по категориям
     *
     * @param int $id
     * @return string
     */
    public function category(int $id): string
    {
        $category = null;

        if ($id) {
            $category = SmilesCategory::query()->where('id', $id)->first();

            if (! $category) {
                abort(404, 'Данной категории не существует!');
            }
        }

        $total = Smile::query()->where('category_id', $id)->count();
        $page = paginate(setting('smilelist'), $total);

        $smiles = Smile::query()
            ->where('category_id', $id)
            ->orderBy(DB::connection()->raw('CHAR_LENGTH(`code`)'))
            ->orderBy('name')
            ->limit($page->limit)
            ->offset($page->offset)
            ->with('category')
            ->get();

        return view('admin/smiles/category', compact('category', 'smiles', 'page'));
    }

    /**
     * Добавление смайла
     *
     * @param Request   $request
     * @param Validator $validator
     * @return string
     */
    public function create(Request $request, Validator $validator): string
    {
        $cid  = int($request->input('cid'));

        if (! is_writable(UPLOADS.'/smiles')){
            abort('default', 'Директория со смайлами недоступна для записи!');
        }

        if ($request->isMethod('post')) {
            $token = check($request->input('token'));
            $code  = check(utfLower($request->input('code')));
            $smile = $request->file('smile');

            $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->length($code, 2, 20, ['code' => 'Слишком длинный или короткий код смайла!'])
                ->regex($code, '|^:+[a-яa-z0-9_\-/\(\)]+$|i', ['code' => 'Код смайла должен начинаться с двоеточия. Разрешены буквы, цифры и дефис!']);

            if ($cid) {
                $category = SmilesCategory::query()->where('id', $cid)->first();
                $validator->notEmpty($category, ['category' => 'Данной категории не существует!']);
            }

            $duplicate = Smile::query()->where('code', $code)->first();
            $validator->empty($duplicate, ['code' => 'Смайл с данным кодом уже имеется в списке!']);

            $rules = [
                'maxsize'   => setting('smilemaxsize'),
                'maxweight' => setting('smilemaxweight'),
                'minweight' => setting('smileminweight'),
            ];

            $validator->file($smile, $rules, ['smile' => 'Не удалось загрузить изображение!']);

            if ($validator->isValid()) {

                $newName = uniqueName($smile->getClientOriginalExtension());
                $path    = (new Smile())->uploadPath . '/' . $newName;
                $smile->move((new Smile())->uploadPath, $newName);

                Smile::query()->create([
                    'category_id' => $cid,
                    'name'        => str_replace(HOME, '', $path),
                    'code'        => $code,
                ]);

                clearCache('smiles');

                setFlash('success', 'Смайл успешно загружен!');
                redirect('/admin/smiles/' . $cid);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $categories = SmilesCategory::query()->get();

        return view('admin/smiles/create', compact('categories', 'cid'));
    }

    /**
     * Редактирование смайла
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     * @return string
     */
    public function edit(int $id, Request $request, Validator $validator): string
    {
        /** @var Smile $smile */
        $smile = Smile::query()->find($id);
        $page = int($request->input('page', 1));

        if (! $smile) {
            abort(404, 'Данного смайла не существует!');
        }

        if ($request->isMethod('post')) {

            $token = check($request->input('token'));
            $code  = check(utfLower($request->input('code')));
            $cid   = int($request->input('cid'));

            $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->length($code, 2, 20, ['code' => 'Слишком длинный или короткий код смайла!'])
                ->regex($code, '|^:+[a-яa-z0-9_\-/\(\)]+$|i', ['code' => 'Код смайла должен начинаться с двоеточия. Разрешены буквы, цифры и дефис!']);

            $duplicate = Smile::query()->where('code', $code)->where('id', '<>', $smile->id)->first();
            $validator->empty($duplicate, ['code' => 'Смайл с данным кодом уже имеется в списке!']);

            if ($cid) {
                $category = SmilesCategory::query()->where('id', $cid)->first();
                $validator->notEmpty($category, ['category' => 'Данной категории не существует!']);
            }

            if ($validator->isValid()) {

                $smile->update([
                    'code'        => $code,
                    'category_id' => $cid,
                ]);

                clearCache('smiles');

                setFlash('success', 'Смайл успешно отредактирован!');
                redirect('/admin/smiles/' . $cid . '?page=' . $page);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $categories = SmilesCategory::query()->get();

        return view('admin/smiles/edit', compact('smile', 'categories', 'page'));
    }

    /**
     * Удаление смайлов
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     * @return void
     * @throws \Exception
     */
    public function delete(int $id, Request $request, Validator $validator): void
    {
        if (! is_writable(UPLOADS . '/smiles')){
            abort('default', 'Директория со смайлами недоступна для записи!');
        }

        $smile = Smile::query()->where('id', $id)->first();

        if (! $smile) {
            abort(404, 'Данного смайла не существует!');
        }

        $page     = int($request->input('page', 1));
        $token    = check($request->input('token'));
        $category = $smile->category->id;

        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!');

        if ($validator->isValid()) {

            deleteFile(HOME . $smile->name, false);
            $smile->delete();

            clearCache('smiles');

            setFlash('success', 'Смайл успешно удален!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/smiles/' . $category . '?page=' . $page);
    }
}
