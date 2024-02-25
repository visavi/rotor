<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Classes\Validator;
use App\Models\PaidAdvert;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaidAdvertController extends AdminController
{
    /**
     * List adverts
     */
    public function index(Request $request): View
    {
        $place = check($request->input('place', 'top_all'));

        if (! in_array($place, PaidAdvert::PLACES, true)) {
            $place = 'top_all';
        }

        $advertTotal = PaidAdvert::query()
            ->selectRaw('place, count(*) as total')
            ->groupBy('place')
            ->pluck('total', 'place')
            ->all();

        $totals = [];
        $places = PaidAdvert::PLACES;
        foreach ($places as $placeName) {
            $totals[$placeName] = $advertTotal[$placeName] ?? 0;
        }

        $adverts = PaidAdvert::query()
            ->where('place', $place)
            ->orderBy('created_at')
            ->with('user')
            ->get();

        return view('admin/paid-adverts/index', compact('adverts', 'place', 'places', 'totals'));
    }

    /**
     * Create advert
     */
    public function create(Request $request, Validator $validator): View|RedirectResponse
    {
        $places = PaidAdvert::PLACES;
        $advert = new PaidAdvert();
        $place = $request->input('place');

        if ($request->isMethod('post')) {
            $site = $request->input('site');
            $names = (array) $request->input('names');
            $color = $request->input('color');
            $bold = empty($request->input('bold')) ? 0 : 1;
            $term = (string) $request->input('term');
            $comment = $request->input('comment');

            $term = strtotime($term);
            $names = array_unique(array_diff($names, ['']));

            $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->in($place, $places, ['place' => __('admin.paid_adverts.place_invalid')])
                ->url($site, ['site' => __('validator.url')])
                ->length($site, 5, 100, ['site' => __('validator.url_text')])
                ->regex($color, '|^#+[A-f0-9]{6}$|', ['color' => __('validator.color')], false)
                ->gt($term, SITETIME, ['term' => __('admin.paid_adverts.term_invalid')])
                ->length($comment, 0, 255, ['comment' => __('validator.text_long')])
                ->gte(count($names), 1, ['names' => __('admin.paid_adverts.names_count')]);

            foreach ($names as $name) {
                $validator->length($name, 5, 35, ['names' => __('validator.text')]);
            }

            if ($validator->isValid()) {
                PaidAdvert::query()->create([
                    'user_id'    => getUser('id'),
                    'place'      => $place,
                    'site'       => $site,
                    'names'      => array_values($names),
                    'color'      => $color,
                    'bold'       => $bold,
                    'comment'    => $comment,
                    'created_at' => SITETIME,
                    'deleted_at' => $term,
                ]);

                clearCache('paidAdverts');

                return redirect('admin/paid-adverts?place=' . $place)
                    ->with('success', __('main.record_added_success'));
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return view('admin/paid-adverts/create', compact('advert', 'places', 'place'));
    }

    /**
     * Change advert
     */
    public function edit(int $id, Request $request, Validator $validator): View|RedirectResponse
    {
        $places = PaidAdvert::PLACES;

        /** @var PaidAdvert $advert */
        $advert = PaidAdvert::query()->find($id);

        if (! $advert) {
            abort(404, __('admin.paid_adverts.not_found'));
        }

        $place = $request->input('place');

        if ($request->isMethod('post')) {
            $site = $request->input('site');
            $names = (array) $request->input('names');
            $color = $request->input('color');
            $bold = empty($request->input('bold')) ? 0 : 1;
            $term = (string) $request->input('term');
            $comment = $request->input('comment');

            $term = strtotime($term);
            $names = array_unique(array_diff($names, ['']));

            $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->in($place, $places, ['place' => __('admin.paid_adverts.place_invalid')])
                ->url($site, ['site' => __('validator.url')])
                ->length($site, 5, 100, ['site' => __('validator.url_text')])
                ->regex($color, '|^#+[A-f0-9]{6}$|', ['color' => __('validator.color')], false)
                ->gt($term, SITETIME, ['term' => __('admin.paid_adverts.term_invalid')])
                ->length($comment, 0, 255, ['comment' => __('validator.text_long')])
                ->gte(count($names), 1, ['names' => __('admin.paid_adverts.names_count')]);

            foreach ($names as $name) {
                $validator->length($name, 5, 35, ['names' => __('validator.text')]);
            }

            if ($validator->isValid()) {
                $advert->update([
                    'place'      => $place,
                    'site'       => $site,
                    'names'      => array_values($names),
                    'color'      => $color,
                    'bold'       => $bold,
                    'comment'    => $comment,
                    'deleted_at' => $term,
                ]);

                clearCache('paidAdverts');

                return redirect('admin/paid-adverts?place=' . $place)
                    ->with('success', __('main.record_saved_success'));
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return view('admin/paid-adverts/edit', compact('advert', 'places', 'place'));
    }

    /**
     * Delete adverts
     */
    public function delete(int $id, Request $request, Validator $validator): RedirectResponse
    {
        /** @var PaidAdvert $advert */
        $advert = PaidAdvert::query()->find($id);

        if (! $advert) {
            abort(404, __('admin.paid_adverts.not_found'));
        }

        $validator->equal($request->input('_token'), csrf_token(), __('validator.token'));

        if ($validator->isValid()) {
            $advert->delete();

            clearCache('paidAdverts');
            setFlash('success', __('main.record_deleted_success'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        return redirect('admin/paid-adverts?place=' . $advert->place);
    }
}
