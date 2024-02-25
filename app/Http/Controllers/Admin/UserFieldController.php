<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\StoreUserFieldRequest;
use App\Models\UserField;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserFieldController extends AdminController
{
    /**
     * List user fields
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
     */
    public function create(): View
    {
        $types = UserField::TYPES;
        $field = new UserField();

        return view('admin/user-fields/create', compact('field', 'types'));
    }

    /**
     *
     */
    public function store(StoreUserFieldRequest $request): RedirectResponse
    {
        UserField::query()->create($request->all());

        return redirect('admin/user-fields')->with('success', __('main.record_added_success'));
    }

    /**
     * Change advert
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
     *
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
     */
    public function destroy(int $id): JsonResponse
    {
        /** @var UserField $field */
        $field = UserField::query()->find($id);

        if (! $field) {
            return response()->json([
                'success' => false,
                'message' => __('admin.user_field.not_found'),
            ]);
        }

        $field->data()->delete();
        $field->delete();

        return response()->json([
            'success' => true,
            'message' => __('main.record_deleted_success'),
        ]);
    }
}
