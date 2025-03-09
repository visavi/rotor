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
            'images' => glob(public_path('uploads/thumbnails/*.{gif,png,jpg,jpeg,webp}'), GLOB_BRACE),
            'views'  => glob(storage_path('framework/views/*.php'), GLOB_BRACE),
            default  => glob(storage_path('framework/cache/data/*/*/*')),
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

        if ($request->input('_token') === csrf_token()) {
            switch ($type) {
                case 'images':
                    Artisan::call('image:clear');
                    break;
                case 'views':
                    Artisan::call('view:clear');
                    break;
                default:
                    Artisan::call('cache:clear');
                    Artisan::call('route:clear');
                    Artisan::call('config:clear');
            }

            setFlash('success', __('admin.caches.success_cleared'));
        } else {
            setFlash('danger', __('validator.token'));
        }

        return redirect('admin/caches?type=' . $type);
    }
}
