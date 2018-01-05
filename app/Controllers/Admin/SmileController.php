<?php

namespace App\Controllers\Admin;

use App\Classes\FileUpload;
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
            ->limit(setting('smilelist'))
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

    }

    /**
     * Удаление смайлов
     */
    public function delete()
    {

    }
}
