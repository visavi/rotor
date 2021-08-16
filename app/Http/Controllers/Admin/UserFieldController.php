<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Classes\Validator;
use App\Models\PaidAdvert;
use App\Models\User;
use App\Models\UserField;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserFieldController extends AdminController
{
    /**
     * List user fields
     *
     * @param Request $request
     *
     * @return View
     */
    public function index(): View
    {
        $fields = UserField::query()
            ->orderBy('sort')
            ->get();

        return view('admin/user-fields/index', compact('fields'));
    }

    /**
     * Create advert
     *
     * @param Request   $request
     * @param Validator $validator
     *
     * @return View|RedirectResponse
     */
    public function create(Request $request, Validator $validator)
    {
        $types = UserField::TYPES;
        $field = new UserField();

        if ($request->isMethod('post')) {
            $type = $request->input('type');
            $sort = int($request->input('sort'));
            $name = $request->input('name');
            $rule = $request->input('rule');

            $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
                /*->in($place, $places, ['place' => __('admin.paid_adverts.place_invalid')])
                ->url($site, ['site' => __('validator.url')])
                ->length($site, 5, 100, ['site' => __('validator.url_text')])
                ->regex($color, '|^#+[A-f0-9]{6}$|', ['color' => __('validator.color')], false)
                ->gt($term, SITETIME, ['term' => __('admin.paid_adverts.term_invalid')])
                ->length($comment, 0, 255, ['comment' => __('validator.text_long')])
                ->gte(count($names), 1, ['names' => __('admin.paid_adverts.names_count')])*/;

            if ($validator->isValid()) {
                UserField::query()->create([
                    'type'  => $type,
                    'sort'  => $sort,
                    'name'  => $name,
                    'rule'  => $rule,
                ]);

                setFlash('success', __('main.record_added_success'));

                return redirect('admin/user-fields');
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return view('admin/user-fields/create', compact('field', 'types'));
    }

    /**
     * Change advert
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     *
     * @return View|RedirectResponse
     */
    public function edit(int $id, Request $request, Validator $validator)
    {
        $types = UserField::TYPES;

        /** @var UserField $field */
        $field = UserField::query()->find($id);

        if (! $field) {
            abort(404, __('admin.user_fields.not_found'));
        }

        if ($request->isMethod('post')) {
            $type = $request->input('type');
            $sort = int($request->input('sort'));
            $name = $request->input('name');
            $rule = $request->input('rule');

            $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
                /*->in($place, $places, ['place' => __('admin.paid_adverts.place_invalid')])
                ->url($site, ['site' => __('validator.url')])
                ->length($site, 5, 100, ['site' => __('validator.url_text')])
                ->regex($color, '|^#+[A-f0-9]{6}$|', ['color' => __('validator.color')], false)
                ->gt($term, SITETIME, ['term' => __('admin.paid_adverts.term_invalid')])
                ->length($comment, 0, 255, ['comment' => __('validator.text_long')])
                ->gte(count($names), 1, ['names' => __('admin.paid_adverts.names_count')])*/;

            if ($validator->isValid()) {
                $field->update([
                    'type'  => $type,
                    'sort'  => $sort,
                    'name'  => $name,
                    'rule'  => $rule,
                ]);

                setFlash('success', __('main.record_saved_success'));

                return redirect('admin/user-fields');
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return view('admin/user-fields/edit', compact('field', 'types'));
    }

    /**
     * Delete field
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     *
     * @return RedirectResponse
     */
    public function delete(int $id, Request $request, Validator $validator): RedirectResponse
    {
        /** @var UserField $field */
        $field = UserField::query()->find($id);

        if (! $field) {
            abort(404, __('admin.user_field.not_found'));
        }

        $validator->equal($request->input('_token'), csrf_token(), __('validator.token'));

        if ($validator->isValid()) {
            $field->delete();

            setFlash('success', __('main.record_deleted_success'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        return redirect('admin/user-fields');
    }
}
