<?php

namespace App\Controllers\Admin;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\Smile;
use App\Models\User;
use Illuminate\Database\Capsule\Manager as DB;
use Intervention\Image\ImageManagerStatic as Image;

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
     */
    public function index()
    {
        $total = Smile::query()->count();
        $page = paginate(setting('smilelist'), $total);

        $smiles = Smile::query()
            ->orderBy(DB::raw('CHAR_LENGTH(`code`)'))
            ->orderBy('name')
            ->limit($page['limit'])
            ->offset($page['offset'])
            ->get();

        return view('admin/smile/index', compact('smiles', 'page'));
    }

    /**
     * Добавление смайла
     */
    public function create()
    {
        if (! is_writable(UPLOADS.'/smiles')){
            abort('default', 'Директория со смайлами недоступна для записи!');
        }

        if (Request::isMethod('post')) {
            $token = check(Request::input('token'));
            $code  = check(utfLower(Request::input('code')));
            $smile = Request::file('smile');

            $validator = new Validator();
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

            $validator->image($smile, $rules, ['smile' => 'Не удалось загрузить изображение!']);

            if ($validator->isValid()) {

                $newName = uniqid() . '.' . $smile->getClientOriginalExtension();
                Image::make($smile)->save(UPLOADS.'/smiles/' . $newName);

                Smile::query()->create([
                    'name' => $newName,
                    'code' => $code,
                ]);

                clearCache();

                setFlash('success', 'Смайл успешно загружен!');
                redirect('/admin/smiles');
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/smile/create');
    }

    /**
     * Редактирование смайла
     */
    public function edit($id)
    {
        $page = int(Request::input('page', 1));

        $smile = Smile::query()->find($id);

        if (! $smile) {
            abort(404, 'Данного смайла не существует!');
        }

        if (Request::isMethod('post')) {

            $token = check(Request::input('token'));
            $code  = check(utfLower(Request::input('code')));

            $validator = new Validator();
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
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/smile/edit', compact('smile', 'page'));
    }

    /**
     * Удаление смайлов
     */
    public function delete()
    {
        if (! is_writable(UPLOADS.'/smiles')){
            abort('default', 'Директория со смайлами недоступна для записи!');
        }

        $page  = int(Request::input('page', 1));
        $token = check(Request::input('token'));
        $del   = intar(Request::input('del'));

        $validator = new Validator();
        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
            ->true($del, 'Отсутствуют выбранные смайлы для удаления!');

        if ($validator->isValid()) {

            $smiles = Smile::query()
                ->whereIn('id', $del)
                ->get();

            if ($smiles->isNotEmpty()) {
                foreach ($smiles as $smile) {
                    deleteImage(UPLOADS.'/smiles/', $smile->name);
                    $smile->delete();
                }
            }

            clearCache();

            setFlash('success', 'Выбранные пользователи успешно удалены!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/smiles?page=' . $page);
    }
}
