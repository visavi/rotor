<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Classes\Validator;
use App\Models\Notice;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;

class NoticeController extends AdminController
{
    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();

        if (! isAdmin(User::BOSS)) {
            abort(403, __('errors.forbidden'));
        }
    }

    /**
     * Главная страница
     *
     * @return string
     */
    public function index(): string
    {
        $notices = Notice::query()
            ->orderBy('id')
            ->with('user')
            ->get();

        return view('admin/notices/index', compact('notices'));
    }

    /**
     * Создание шаблона
     *
     * @param Request   $request
     * @param Validator $validator
     *
     * @return string
     */
    public function create(Request $request, Validator $validator): string
    {
        if ($request->isMethod('post')) {
            $type    = $request->input('type');
            $name    = $request->input('name');
            $text    = $request->input('text');
            $protect = empty($request->input('protect')) ? 0 : 1;

            $validator->equal($request->input('token'), $_SESSION['token'], __('validator.token'))
                ->regex($type, '|^[a-z0-9_\-]+$|i', ['type' => 'Недопустимое название типа шаблона!'])
                ->length($type, 3, 20, ['type' => __('admin.notices.notice_length')])
                ->length($name, 5, 100, ['name' => __('validator.text')])
                ->length($text, 10, 65000, ['text' => __('validator.text')]);

            $duplicate = Notice::query()->where('type', $type)->first();
            $validator->empty($duplicate, ['type' => __('admin.notices.notice_exists')]);

            if ($validator->isValid()) {
                /** @var Notice $notice */
                $notice = Notice::query()->create([
                    'type'       => $type,
                    'name'       => $name,
                    'text'       => $text,
                    'user_id'    => getUser('id'),
                    'protect'    => $protect,
                    'created_at' => SITETIME,
                    'updated_at' => SITETIME,
                ]);

                setFlash('success', __('admin.notices.notice_success_saved'));
                redirect('/admin/notices/edit/' . $notice->id);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/notices/create');
    }

    /**
     * Редактирование шаблона
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     *
     * @return string
     */
    public function edit(int $id, Request $request, Validator $validator): string
    {
        /** @var Notice $notice */
        $notice = Notice::query()->find($id);

        if (! $notice) {
            abort(404, __('admin.notices.notice_not_found'));
        }

        if ($request->isMethod('post')) {
            $name    = $request->input('name');
            $text    = $request->input('text');
            $protect = empty($request->input('protect')) ? 0 : 1;

            $validator->equal($request->input('token'), $_SESSION['token'], __('validator.token'))
                ->length($name, 5, 100, ['name' => __('validator.text')])
                ->length($text, 10, 65000, ['text' => __('validator.text')]);

            if ($validator->isValid()) {
                $notice->update([
                    'name'       => $name,
                    'text'       => $text,
                    'user_id'    => getUser('id'),
                    'protect'    => $protect,
                    'updated_at' => SITETIME,
                ]);

                setFlash('success', __('admin.notices.notice_success_saved'));
                redirect('/admin/notices/edit/' . $notice->id);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/notices/edit', compact('notice'));
    }

    /**
     * Удаление шаблона
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     *
     * @return void
     * @throws Exception
     */
    public function delete(int $id, Request $request, Validator $validator): void
    {
        /** @var Notice $notice */
        $notice = Notice::query()->find($id);

        $validator->equal($request->input('token'), $_SESSION['token'], __('validator.token'))
            ->notEmpty($notice, __('admin.notices.notice_not_found'))
            ->empty($notice->protect, __('admin.notices.notice_protect'));

        if ($validator->isValid()) {
            $notice->delete();

            setFlash('success', __('admin.notices.notice_success_deleted'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/notices');
    }
}
