<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\ModuleRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ModuleRegistryController extends AdminController
{
    public function index(): View
    {
        $registries = ModuleRegistry::query()->orderByDesc('created_at')->get();

        return view('admin/modules/registries', compact('registries'));
    }

    public function store(Request $request): RedirectResponse
    {
        $url = trim($request->input('url', ''));

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            setFlash('danger', __('admin.registries.invalid_url'));

            return redirect()->route('admin.registries.index');
        }

        if (ModuleRegistry::query()->where('url', $url)->exists()) {
            setFlash('danger', __('admin.registries.already_exists'));

            return redirect()->route('admin.registries.index');
        }

        $registry = ModuleRegistry::query()->create([
            'url' => $url,
        ]);

        $registry->fetch(force: true);

        if ($registry->fetchFailed) {
            setFlash('danger', __('admin.registries.registry_fetch_failed'));
        } else {
            setFlash('success', __('admin.registries.registry_success_added'));
        }

        return redirect()->route('admin.registries.index');
    }

    public function refresh(int $id): RedirectResponse
    {
        $registry = ModuleRegistry::query()->findOrFail($id);
        $registry->fetch(force: true);

        if ($registry->fetchFailed) {
            setFlash('danger', __('admin.registries.registry_fetch_failed'));
        } else {
            setFlash('success', __('admin.registries.registry_success_refreshed'));
        }

        return redirect()->route('admin.registries.index');
    }

    public function toggle(int $id): RedirectResponse
    {
        $registry = ModuleRegistry::query()->findOrFail($id);
        $registry->update(['active' => ! $registry->active]);

        return redirect()->route('admin.registries.index');
    }

    public function destroy(int $id): RedirectResponse
    {
        ModuleRegistry::query()->findOrFail($id)->delete();

        setFlash('success', __('admin.registries.registry_success_deleted'));

        return redirect()->route('admin.registries.index');
    }
}
