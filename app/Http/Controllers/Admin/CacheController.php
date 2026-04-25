<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class CacheController extends AdminController
{
    /**
     * Главная страница
     */
    public function index(Request $request): View
    {
        $type = $request->input('type', 'files');

        $files = match ($type) {
            'views' => glob(storage_path('framework/views/*.php'), GLOB_BRACE),
            default => glob(storage_path('framework/cache/data/*/*/*')),
        };

        $files = paginate($files, 20, compact('type'));

        return view('admin/caches/index', compact('files', 'type'));
    }

    /**
     * Очистка кеша
     */
    public function clear(Request $request): RedirectResponse
    {
        $type = $request->input('type');

        if ($type === 'views') {
            Artisan::call('view:clear');
        } else {
            Artisan::call('cache:clear');
            Artisan::call('route:clear');
            Artisan::call('config:clear');
        }

        setFlash('success', __('admin.caches.success_cleared'));

        return redirect('admin/caches?type=' . $type);
    }
}
