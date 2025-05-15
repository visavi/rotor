<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Classes\Validator;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OfferController extends AdminController
{
    /**
     * Главная страница
     */
    public function index(Request $request, string $type = Offer::OFFER): View
    {
        $otherType = $type === Offer::OFFER ? Offer::ISSUE : Offer::OFFER;
        $otherCount = Offer::query()->where('type', $otherType)->count();

        $sort = check($request->input('sort', 'rating'));
        $order = match ($sort) {
            'time'     => 'created_at',
            'status'   => 'status',
            'comments' => 'count_comments',
            default    => 'rating',
        };

        $offers = Offer::query()
            ->where('type', $type)
            ->orderByDesc($order)
            ->with('user')
            ->paginate(setting('postoffers'))
            ->appends(['sort' => $sort]);

        return view('admin/offers/index', compact('offers', 'order', 'type', 'otherCount'));
    }

    /**
     * Просмотр записи
     */
    public function view(int $id): View
    {
        $offer = Offer::query()
            ->where('offers.id', $id)
            ->first();

        if (! $offer) {
            abort(404, __('main.record_not_found'));
        }

        return view('admin/offers/view', compact('offer'));
    }

    /**
     * Редактирование записи
     *
     *
     * @return View|RedirectResponse
     */
    public function edit(int $id, Request $request, Validator $validator)
    {
        $offer = Offer::query()->where('id', $id)->first();

        if (! $offer) {
            abort(404, __('main.record_not_found'));
        }

        if ($request->isMethod('post')) {
            $title = $request->input('title');
            $text = $request->input('text');
            $type = $request->input('type');
            $closed = empty($request->input('closed')) ? 0 : 1;

            $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->length($title, 3, 50, ['title' => __('validator.text')])
                ->length($text, 5, 1000, ['text' => __('validator.text')])
                ->in($type, Offer::TYPES, ['type' => __('offers.type_invalid')]);

            if ($validator->isValid()) {
                $title = antimat($title);
                $text = antimat($text);

                $offer->update([
                    'type'       => $type,
                    'title'      => $title,
                    'text'       => $text,
                    'closed'     => $closed,
                    'updated_at' => SITETIME,
                ]);

                setFlash('success', __('main.record_changed_success'));

                return redirect('admin/offers/' . $offer->id);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return view('admin/offers/edit', compact('offer'));
    }

    /**
     * Ответ на предложение
     *
     *
     * @return View|RedirectResponse
     */
    public function reply(int $id, Request $request, Validator $validator)
    {
        $offer = Offer::query()->where('id', $id)->first();

        if (! $offer) {
            abort(404, __('main.record_not_found'));
        }

        if ($request->isMethod('post')) {
            $reply = $request->input('reply');
            $status = $request->input('status');
            $closed = empty($request->input('closed')) ? 0 : 1;

            $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->length($reply, 5, 3000, ['reply' => __('validator.text')])
                ->in($status, Offer::STATUSES, ['status' => __('offers.status_invalid')]);

            if ($validator->isValid()) {
                $reply = antimat($reply);

                $offer->update([
                    'reply'         => $reply,
                    'reply_user_id' => getUser('id'),
                    'status'        => $status,
                    'closed'        => $closed,
                    'updated_at'    => SITETIME,
                ]);

                setFlash('success', __('offers.answer_success_added'));

                return redirect('admin/offers/' . $offer->id);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        $statuses = Offer::STATUSES;

        return view('admin/offers/reply', compact('offer', 'statuses'));
    }

    /**
     * Пересчет комментариев
     */
    public function restatement(Request $request): RedirectResponse
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, __('errors.forbidden'));
        }

        if ($request->input('_token') === csrf_token()) {
            restatement('offers');

            setFlash('success', __('main.success_recounted'));
        } else {
            setFlash('danger', __('validator.token'));
        }

        return redirect('admin/offers');
    }

    /**
     * Удаление записей
     */
    public function delete(Request $request, Validator $validator): RedirectResponse
    {
        $page = int($request->input('page', 1));
        $del = intar($request->input('del'));
        $type = $request->input('type') === Offer::OFFER ? Offer::OFFER : Offer::ISSUE;

        $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
            ->true($del, __('validator.deletion'));

        if ($validator->isValid()) {
            $offers = Offer::query()->whereIn('id', $del)->get();

            $offers->each(static function (Offer $offer) {
                $offer->delete();
            });

            setFlash('success', __('main.records_deleted_success'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        return redirect('admin/offers/' . $type . '?page=' . $page);
    }
}
