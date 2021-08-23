<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\StoreUserFieldRequest;
use App\Models\UserField;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserFieldController extends AdminController
{
    /**
     * List user fields
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
     * @return View
     */
    public function create(): View
    {
        $types = UserField::TYPES;
        $field = new UserField();

        return view('admin/user-fields/create', compact('field', 'types'));
    }

    /**
     *
     *
     * @param StoreUserFieldRequest $request
     *
     * @return RedirectResponse
     */
    public function store(StoreUserFieldRequest $request): RedirectResponse
    {
        UserField::query()->create($request->all());

        return redirect('admin/user-fields')->with('success', __('main.record_added_success'));
    }

    /**
     * Change advert
     *
     * @param int $id
     *
     * @return View
     */
    public function edit(int $id): View
    {
        $types = UserField::TYPES;

        /** @var UserField $field */
        $field = UserField::query()->find($id);

        if (! $field) {
            abort(404, __('admin.user_fields.not_found'));
        }

        return view('admin/user-fields/edit', compact('field', 'types'));
    }

    /**
     * @param int                   $id
     * @param StoreUserFieldRequest $request
     *
     * @return RedirectResponse
     */
    public function update(int $id, StoreUserFieldRequest $request): RedirectResponse
    {
        $field = UserField::query()->find($id);

        if (! $field) {
            abort(404, __('admin.user_fields.not_found'));
        }

        $field->update($request->all());

        return redirect('admin/user-fields')->with('success', __('main.record_saved_success'));
    }

    /**
     * Delete field
     *
     * @param int $id
     *
     * @return bool
     */
    public function destroy(int $id): bool
    {
        /** @var UserField $field */
        $field = UserField::query()->find($id);

        if (! $field) {
            abort(404, __('admin.user_field.not_found'));
        }

        $field->data()->delete();
        $field->delete();

/*            setFlash('success', __('main.record_deleted_success'));
        } else {
            setFlash('danger', $validator->getErrors());
        }*/

        return true;
    }
}
