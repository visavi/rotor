<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Classes\Validator;
use App\Models\BlackList;
use App\Models\User;
use Illuminate\Http\Request;

class BlacklistController extends AdminController
{
    /**
     * @var string
     */
    private $type;

    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();

        if (! isAdmin(User::ADMIN)) {
            abort(403, trans('errors.forbidden'));
        }

        $types = ['email', 'login', 'domain'];

        $request    = Request::createFromGlobals();
        $this->type = $request->input('type', 'email');

        if (! in_array($this->type, $types, true)) {
            abort(404, trans('admin.blacklists.type_not_found'));
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
        $type = $this->type;

        if ($request->isMethod('post')) {
            $token = check($request->input('token'));
            $value = check(utfLower($request->input('value')));

            $validator->equal($token, $_SESSION['token'], trans('validator.token'))
                ->length($value, 1, 100, ['value' => trans('validator.text')]);

            if ($type === 'email') {
                $validator->regex($value, '#^([a-z0-9_\-\.])+\@([a-z0-9_\-\.])+(\.([a-z0-9])+)+$#', ['value' => trans('validator.email')]);
            }

            if ($type === 'login') {
                $validator->regex($value, '|^[a-z0-9\-]+$|', ['value' => trans('admin.blacklists.invalid_login')]);
            }

            if ($type === 'domain') {
                $value = siteDomain($value);
                $validator->regex($value, '#([а-яa-z0-9_\-\.])+(\.([а-яa-z0-9\/])+)+$#u', ['value' => trans('validator.site')]);
            }

            $duplicate = BlackList::query()->where('type', $type)->where('value', $value)->first();
            $validator->empty($duplicate, ['value' => trans('main.record_exists')]);

            if ($validator->isValid()) {

                BlackList::query()->create([
                    'type'       => $type,
                    'value'      => $value,
                    'user_id'    => getUser('id'),
                    'created_at' => SITETIME,
                ]);

                setFlash('success', trans('main.record_added_success'));
                redirect('/admin/blacklists?type=' . $type);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $total = BlackList::query()->where('type', $type)->count();
        $page = paginate(setting('blacklist'), $total);

        $lists = BlackList::query()
            ->where('type', $type)
            ->orderBy('created_at', 'desc')
            ->limit($page->limit)
            ->offset($page->offset)
            ->with('user')
            ->get();

        return view('admin/blacklists/index', compact('lists', 'type', 'page'));
    }

    /**
     * Удаление записей
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
        $type  = $this->type;

        $validator->equal($token, $_SESSION['token'], trans('validator.token'))
            ->true($del, trans('validator.deletion'));

        if ($validator->isValid()) {
            BlackList::query()->where('type', $type)->whereIn('id', $del)->delete();

            setFlash('success', trans('main.records_deleted_success'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/blacklists?type=' . $type . '&page=' . $page);
    }
}
