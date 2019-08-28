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
            abort(403, trans('errors.forbidden'));
        }
    }

    /**
     * Главная страница
     *
     * @param Request $request
     * @return string
     */
    public function index(Request $request): string
    {
        $type = check($request->input('type', 'files'));

        if ($type === 'files') {
            $files = glob(STORAGE . '/temp/*.dat');

            $view = view('admin/caches/index', compact('files'));
        } else {
            $images = glob(UPLOADS.'/thumbnails/*.{gif,png,jpg,jpeg}', GLOB_BRACE);
            $page   = paginate(20, count($images));

            $images = \array_slice($images, $page->offset, $page->limit);

            $view = view('admin/caches/images', compact('images', 'page'));
        }

        return $view;
    }

    /**
     * Очистка кеша
     *
     * @param Request $request
     * @return void
     */
    public function clear(Request $request): void
    {
        $token = check($request->input('token'));
        $type  = check($request->input('type', 'files'));

        if ($token === $_SESSION['token']) {

            if ($type === 'files') {
                clearCache();
            } else {
                $images = glob(UPLOADS.'/thumbnails/*.{gif,png,jpg,jpeg}', GLOB_BRACE);

                if ($images) {
                    foreach ($images as $image) {
                        unlink ($image);
                    }
                }
            }

            setFlash('success', 'Кеш успешно очищен!');
        } else {
            setFlash('danger', trans('validator.token'));
        }

        redirect('/admin/caches?type=' . $type);
    }
}
