<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Classes\Validator;
use App\Models\Advert;
use App\Models\PaidAdvert;
use App\Models\User;
use Illuminate\Http\Request;

class PaidAdvertController extends AdminController
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
        $place = check($request->input('place', 'top_all'));

        if (! in_array($place, PaidAdvert::PLACES, true)) {
            $place = 'top_all';
        }

        $advertTotal = PaidAdvert::query()
            ->selectRaw('place, count(*) as total')
            ->where('deleted_at', '>', SITETIME)
            ->groupBy('place')
            ->pluck('total', 'place')
            ->all();


        $totals = [];
        $places = PaidAdvert::PLACES;
        foreach ($places as $placeName) {
            $totals[$placeName] = $advertTotal[$placeName] ?? 0;
        }

        $adverts = PaidAdvert::query()
            ->where('deleted_at', '>', SITETIME)
            ->where('place', $place)
            ->orderBy('created_at')
            ->with('user')
            ->get();

        return view('admin/paid-adverts/index', compact('adverts', 'place', 'places', 'totals'));
    }

    /**
     * Покупка рекламы
     *
     * @param Request   $request
     * @param Validator $validator
     *
     * @return string
     */
    public function create(Request $request, Validator $validator): string
    {
        $places = PaidAdvert::PLACES;

        if ($request->isMethod('post')) {
            $site  = $request->input('site');
            $place = $request->input('place');
            $names = (array) $request->input('names');
            $color = $request->input('color');
            $bold  = empty($request->input('bold')) ? 0 : 1;
            $term  = (string) $request->input('term');
            $comment = $request->input('comment');

            $term = strtotime($term);

            $validator->equal($request->input('token'), $_SESSION['token'], __('validator.token'))
                ->in($place, $places, ['place' => 'Неверно'])
                ->url($site, ['site' => __('validator.url')])
                ->length($site, 5, 100, ['site' => __('validator.url_text')])
                ->regex($color, '|^#+[A-f0-9]{6}$|', ['color' => __('validator.color')], false)
                ->gt($term, SITETIME, ['term' => 'term > current time'])
                ->length($comment, 0, 1000, ['comment' => __('validator.text_long')]);

            $names = array_unique(array_diff($names, ['']));

            foreach ($names as $name) {
                $validator->length($name, 5, 35, ['names' => __('validator.text')]);
            }

            if ($validator->isValid()) {
                PaidAdvert::query()->where('deleted_at', '<', SITETIME)->delete();

                PaidAdvert::query()->create([
                    'user_id'    => getUser('id'),
                    'place'      => $place,
                    'site'       => $site,
                    'names'      => array_values($names),
                    'color'      => $color,
                    'bold'       => $bold,
                    'created_at' => SITETIME,
                    'deleted_at' => $term,
                ]);

                clearCache('paidAdverts');
                setFlash('success', __('adverts.advert_success_posted'));
                redirect('/admin/paid-adverts');
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/paid-adverts/create', compact('places'));
    }
}
