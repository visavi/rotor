<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Classes\Validator;
use App\Models\BlackList;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlacklistController extends AdminController
{
    private string $type;

    /**
     * Конструктор
     */
    public function __construct(Request $request)
    {
        $types = ['email', 'login', 'domain'];

        $this->type = $request->input('type', 'email');

        if (! in_array($this->type, $types, true)) {
            abort(404, __('admin.blacklists.type_not_found'));
        }
    }

    /**
     * Главная страница
     *
     *
     * @return View|RedirectResponse
     */
    public function index(Request $request, Validator $validator)
    {
        $type = $this->type;

        if ($request->isMethod('post')) {
            $value = utfLower($request->input('value'));

            $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->length($value, 1, 100, ['value' => __('validator.text')]);

            if ($type === 'email') {
                $validator->regex($value, '#^([a-z0-9_\-\.])+\@([a-z0-9_\-\.])+(\.([a-z0-9])+)+$#', ['value' => __('validator.email')]);
            }

            if ($type === 'login') {
                $validator->regex($value, '|^[a-z0-9\-]+$|', ['value' => __('admin.blacklists.invalid_login')])
                ->length($value, 3, 20, ['value' => __('validator.text')]);
            }

            if ($type === 'domain') {
                $value = siteDomain($value);
                $validator->regex($value, '#([а-яa-z0-9_\-\.])+(\.([а-яa-z0-9\/])+)+$#u', ['value' => __('validator.site')]);
            }

            $duplicate = BlackList::query()->where('type', $type)->where('value', $value)->first();
            $validator->empty($duplicate, ['value' => __('main.record_exists')]);

            if ($validator->isValid()) {
                BlackList::query()->create([
                    'type'       => $type,
                    'value'      => $value,
                    'user_id'    => getUser('id'),
                    'created_at' => SITETIME,
                ]);

                setFlash('success', __('main.record_added_success'));

                return redirect('admin/blacklists?type=' . $type);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        $lists = BlackList::query()
            ->where('type', $type)
            ->orderByDesc('created_at')
            ->with('user')
            ->paginate(setting('blacklist'))
            ->appends(['type' => $type]);

        return view('admin/blacklists/index', compact('lists', 'type'));
    }

    /**
     * Удаление записей
     */
    public function delete(Request $request, Validator $validator): RedirectResponse
    {
        $page = int($request->input('page', 1));
        $del = intar($request->input('del'));
        $type = $this->type;

        $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
            ->true($del, __('validator.deletion'));

        if ($validator->isValid()) {
            BlackList::query()->where('type', $type)->whereIn('id', $del)->delete();

            setFlash('success', __('main.records_deleted_success'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        return redirect('admin/blacklists?type=' . $type . '&page=' . $page);
    }
}
