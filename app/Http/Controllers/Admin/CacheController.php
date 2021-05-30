<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CacheController extends AdminController
{
    /**
     * Главная страница
     *
     * @param Request $request
     *
     * @return View
     */
    public function index(Request $request): View
    {
        $type = $request->input('type', 'files');

        if ($type === 'images') {
            $files = glob(public_path('uploads/thumbnails/*.{gif,png,jpg,jpeg}'), GLOB_BRACE);
            $files = paginate($files, 20, compact('type'));
        } elseif ($type === 'views') {
            $files = glob(storage_path('views/*.php'), GLOB_BRACE);
            $files = paginate($files, 20, compact('type'));
        } else {
            $files = glob(storage_path('caches/{*/*/*,*.php}'), GLOB_BRACE);
            $files = paginate($files, 20, compact('type'));
        }

        return view('admin/caches/index', compact('files', 'type'));
    }

    /**
     * Очистка кеша
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function clear(Request $request): RedirectResponse
    {
        $type = $request->input('type');

        if ($request->input('_token') === csrf_token()) {
            switch ($type) {
                case 'images':
                    runCommand(new ImageClear());
                    break;
                case 'views':
                    runCommand(new ViewClear());
                    break;
                default:
                    runCommand(new ConfigClear());
                    runCommand(new RouteClear());
                    runCommand(new CacheClear());
            }

            setFlash('success', __('admin.caches.success_cleared'));
        } else {
            setFlash('danger', __('validator.token'));
        }

        return redirect('admin/caches?type=' . $type);
    }
}
