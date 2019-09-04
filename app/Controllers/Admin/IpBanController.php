<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Classes\Validator;
use App\Models\Ban;
use App\Models\User;
use Illuminate\Http\Request;

class IpBanController extends AdminController
{
    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();

        if (! isAdmin(User::ADMIN)) {
            abort(403, __('errors.forbidden'));
        }
    }

    /**
     * Главная страница
     *
     * @param Request   $request
     * @param Validator $validator
     * @return string
     */
    public function index(Request $request, Validator $validator): string
    {
        if ($request->isMethod('post')) {
            $token = check($request->input('token'));
            $ip    = check($request->input('ip'));

            $validator->equal($token, $_SESSION['token'], __('validator.token'))
                ->regex($ip, '|^[0-9]{1,3}\.[0-9,*]{1,3}\.[0-9,*]{1,3}\.[0-9,*]{1,3}$|', ['ip' => 'Вы ввели недопустимый IP-адрес для бана!']);

            $duplicate = Ban::query()->where('ip', $ip)->first();
            $validator->empty($duplicate, ['ip' => 'Введенный IP уже имеетеся в списке!']);

            if ($validator->isValid()) {

                Ban::query()->create([
                    'ip'         => $ip,
                    'user_id'    => getUser('id'),
                    'created_at' => SITETIME,
                ]);

                ipBan(true);

                setFlash('success', 'IP успешно занесен в список!');
                redirect('/admin/ipbans');
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $total = Ban::query()->count();
        $page = paginate(setting('ipbanlist'), $total);

        $logs = Ban::query()
            ->orderBy('created_at', 'desc')
            ->limit($page->limit)
            ->offset($page->offset)
            ->with('user')
            ->get();

        return view('admin/ipbans/index', compact('logs', 'page'));
    }

    /**
     * Удаление ip
     *
     * @param Request   $request
     * @param Validator $validator
     * @return void
     */
    public function delete(Request $request, Validator $validator): void
    {
        $page  = int($request->input('page', 1));
        $token = check($request->input('token'));
        $del   = intar($request->input('del'));

        $validator->equal($token, $_SESSION['token'], __('validator.token'))
            ->true($del, __('validator.deletion'));

        if ($validator->isValid()) {

            Ban::query()->whereIn('id', $del)->delete();
            ipBan(true);

            setFlash('success', 'Выбранные IP успешно удалены из списка!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/ipbans?page=' . $page);
    }

    /**
     * Очистка ip
     *
     * @param Request   $request
     * @param Validator $validator
     * @return void
     */
    public function clear(Request $request, Validator $validator): void
    {
        $token = check($request->input('token'));

        $validator
            ->equal($token, $_SESSION['token'], __('validator.token'))
            ->true(isAdmin(User::BOSS), 'Очищать список IP может только владелец!');

        if ($validator->isValid()) {

            Ban::query()->truncate();
            ipBan(true);

            setFlash('success', 'Список IP успешно очищен!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/ipbans');
    }
}
