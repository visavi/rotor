<?php

namespace App\Controllers\Admin;

use App\Classes\Validator;
use App\Models\Smile;
use App\Models\User;
use Illuminate\Database\Capsule\Manager as DB;
use Intervention\Image\ImageManagerStatic as Image;
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
        $total = Smile::query()->count();
        $page = paginate(setting('smilelist'), $total);

        $smiles = Smile::query()
            ->orderBy(DB::raw('CHAR_LENGTH(`code`)'))
            ->orderBy('name')
            ->limit($page->limit)
            ->offset($page->offset)
            ->get();

        return view('admin/smiles/index', compact('smiles', 'page'));
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
                Image::make($smile)->save($path);

                Smile::query()->create([
                    'name' => str_replace(HOME, '', $path),
                    'code' => $code,
                ]);

                clearCache();

                setFlash('success', 'Смайл успешно загружен!');
                redirect('/admin/smiles');
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/smiles/create');
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

            $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->length($code, 2, 20, ['code' => 'Слишком длинный или короткий код смайла!'])
                ->regex($code, '|^:+[a-яa-z0-9_\-/\(\)]+$|i', ['code' => 'Код смайла должен начинаться с двоеточия. Разрешены буквы, цифры и дефис!']);

            $duplicate = Smile::query()->where('code', $code)->where('id', '<>', $smile->id)->first();
            $validator->empty($duplicate, ['code' => 'Смайл с данным кодом уже имеется в списке!']);

            if ($validator->isValid()) {

                $smile->update([
                    'code' => $code,
                ]);

                clearCache();

                setFlash('success', 'Смайл успешно отредактирован!');
                redirect('/admin/smiles?page=' . $page);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/smiles/edit', compact('smile', 'page'));
    }

    /**
     * Удаление смайлов
     *
     * @param Request   $request
     * @param Validator $validator
     * @return void
     */
    public function delete(Request $request, Validator $validator): void
    {
        if (! is_writable(UPLOADS . '/smiles')){
            abort('default', 'Директория со смайлами недоступна для записи!');
        }

        $page  = int($request->input('page', 1));
        $token = check($request->input('token'));
        $del   = intar($request->input('del'));

        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
            ->true($del, 'Отсутствуют выбранные смайлы для удаления!');

        if ($validator->isValid()) {

            $smiles = Smile::query()
                ->whereIn('id', $del)
                ->get();

            if ($smiles->isNotEmpty()) {
                foreach ($smiles as $smile) {
                    deleteFile(HOME . $smile->name);
                    $smile->delete();
                }
            }

            clearCache();

            setFlash('success', 'Выбранные смайлы успешно удалены!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/smiles?page=' . $page);
    }
}
