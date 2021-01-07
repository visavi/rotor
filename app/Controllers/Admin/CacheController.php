<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;

class CacheController extends AdminController
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
     * @param Request $request
     *
     * @return string
     */
    public function index(Request $request): string
    {
        $type = $request->input('type', 'files');

        if ($type === 'files') {
            $files = glob(STORAGE . '/caches/{*/*/*,*.php}', GLOB_BRACE);
            $files = paginate($files, 20, compact('type'));

            $view = view('admin/caches/index', compact('files'));
        } else {
            $images = glob(UPLOADS . '/thumbnails/*.{gif,png,jpg,jpeg}', GLOB_BRACE);
            $images = paginate($images, 20, compact('type'));

            $view = view('admin/caches/images', compact('images'));
        }

        return $view;
    }

    /**
     * Очистка кеша
     *
     * @param Request $request
     *
     * @return void
     */
    public function clear(Request $request): void
    {

        $type = $request->input('type', 'files');

        if ($request->input('token') === $_SESSION['token']) {
            if ($type === 'files') {
                clearCache();
            } else {
                $images = glob(UPLOADS.'/thumbnails/*.{gif,png,jpg,jpeg}', GLOB_BRACE);

                if ($images) {
                    foreach ($images as $image) {
                        unlink($image);
                    }
                }
            }

            setFlash('success', __('admin.caches.success_cleared'));
        } else {
            setFlash('danger', __('validator.token'));
        }

        redirect('/admin/caches?type=' . $type);
    }
}
