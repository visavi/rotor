<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Classes\Validator;
use App\Models\AdminAdvert;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAdvertController extends AdminController
{
    /**
     * Главная страница
     *
     * @param Request   $request
     * @param Validator $validator
     *
     * @return View
     */
    public function index(Request $request, Validator $validator): View
    {
        $advert = AdminAdvert::query()
            ->where('user_id', getUser('id'))
            ->firstOrNew([]);

        if ($request->isMethod('post')) {
            $site  = $request->input('site');
            $name  = $request->input('name');
            $color = $request->input('color');
            $bold  = empty($request->input('bold')) ? 0 : 1;

            $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->regex($site, '|^https?://([а-яa-z0-9_\-\.])+(\.([а-яa-z0-9\/\-?_=#])+)+$|iu', ['site' => __('validator.url')])
                ->length($site, 5, 100, ['site' => __('validator.url_text')])
                ->length($name, 5, 35, ['name' => __('validator.text')])
                ->regex($color, '|^#+[A-f0-9]{6}$|', ['color' => __('validator.color')], false);

            if ($validator->isValid()) {
                $advert = AdminAdvert::query()
                    ->updateOrCreate([], [
                        'site'  => $site,
                        'name'  => $name,
                        'color' => $color,
                        'bold'  => $bold,
                        'user_id'    => getUser('id'),
                        'created_at' => SITETIME,
                        'deleted_at' => SITETIME + 7 * 86400,
                    ]);

                clearCache('adminAdverts');
                setFlash('success', __('main.record_saved_success'));
                redirect('/admin/admin-adverts');
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/admin-adverts/index', compact('advert'));
    }
}
