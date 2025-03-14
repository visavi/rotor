<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Classes\Validator;
use App\Models\Advert;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdvertController extends Controller
{
    public ?User $user;

    /**
     * Конструктор
     */
    public function __construct()
    {
        $this->middleware('check.user');

        $this->middleware(function ($request, $next) {
            $this->user = getUser();

            return $next($request);
        });

        if (! setting('rekusershow')) {
            abort(200, __('adverts.advert_closed'));
        }
    }

    /**
     * Главная страница
     */
    public function index(): View
    {
        $adverts = Advert::query()
            ->where('deleted_at', '>', SITETIME)
            ->orderByDesc('deleted_at')
            ->with('user')
            ->paginate(setting('rekuserpost'));

        return view('adverts/index', compact('adverts'));
    }

    /**
     * Покупка рекламы
     *
     *
     * @return View|RedirectResponse
     */
    public function create(Request $request, Validator $validator)
    {
        if ($this->user->point < setting('rekuserpoint')) {
            abort(200, __('adverts.advert_point', ['point' => plural(50, setting('scorename'))]));
        }

        Advert::query()->where('deleted_at', '<', SITETIME)->delete();

        $total = Advert::query()->count();
        if ($total >= setting('rekusertotal')) {
            abort(200, __('adverts.advert_not_seats'));
        }

        $advert = Advert::query()
            ->where('user_id', $this->user->id)
            ->first();

        if ($advert) {
            abort(200, __('adverts.advert_already_posted'));
        }

        if ($request->isMethod('post')) {
            $site = $request->input('site');
            $name = $request->input('name');
            $color = $request->input('color');
            $bold = empty($request->input('bold')) ? 0 : 1;

            $price = setting('rekuserprice');

            if ($color) {
                $price += setting('rekuseroptprice');
            }

            if ($bold) {
                $price += setting('rekuseroptprice');
            }

            $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->gte($this->user->point, setting('rekuserpoint'), __('adverts.advert_point', ['point' => plural(50, setting('scorename'))]))
                ->true(captchaVerify(), ['protect' => __('validator.captcha')])
                ->regex($site, '|^https?://([а-яa-z0-9_\-\.])+(\.([а-яa-z0-9\/\-?_=#])+)+$|iu', ['site' => __('validator.url')])
                ->length($site, 5, 100, ['site' => __('validator.url_text')])
                ->length($name, 5, 35, ['name' => __('validator.text')])
                ->regex($color, '|^#+[A-f0-9]{6}$|', ['color' => __('validator.color')], false)
                ->gte($this->user->money, $price, __('adverts.advert_not_money'));

            if ($validator->isValid()) {
                Advert::query()->create([
                    'site'       => $site,
                    'name'       => $name,
                    'color'      => $color,
                    'bold'       => $bold,
                    'user_id'    => $this->user->id,
                    'created_at' => SITETIME,
                    'deleted_at' => strtotime('+' . setting('rekusertime') . ' hours', SITETIME),
                ]);

                $this->user->decrement('money', $price);

                clearCache('adverts');
                setFlash('success', __('adverts.advert_success_posted'));

                return redirect('adverts');
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return view('adverts/create');
    }
}
