<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Commands\CacheClear;
use App\Commands\ConfigClear;
use App\Commands\ImageClear;
use App\Commands\RouteClear;
use App\Commands\ViewClear;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

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
     * @return View
     */
    public function index(Request $request): View
    {
        $type = $request->input('type', 'files');

        if ($type === 'images') {
            $files = glob(UPLOADS . '/thumbnails/*.{gif,png,jpg,jpeg}', GLOB_BRACE);
            $files = paginate($files, 20, compact('type'));
        } elseif ($type === 'views') {
            $files = glob(STORAGE . '/views/*.php', GLOB_BRACE);
            $files = paginate($files, 20, compact('type'));
        } else {
            $files = glob(STORAGE . '/caches/{*/*/*,*.php}', GLOB_BRACE);
            $files = paginate($files, 20, compact('type'));
        }

        return view('admin/caches/index', compact('files', 'type'));
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

        redirect('/admin/caches?type=' . $type);
    }
}
