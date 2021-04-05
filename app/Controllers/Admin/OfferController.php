<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Classes\Validator;
use App\Models\Comment;
use App\Models\Offer;
use App\Models\Polling;
use App\Models\User;
use Illuminate\Http\Request;

class OfferController extends AdminController
{
    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();

        if (! isAdmin(User::ADMIN)) {
            abort(403, __('errors.forbidden'));
        }
    }

    /**
     * Главная страница
     *
     * @param string  $type
     * @param Request $request
     *
     * @return string
     */
    public function index(Request $request, $type = Offer::OFFER): string
    {
        $otherType  = $type === Offer::OFFER ? Offer::ISSUE : Offer::OFFER;
        $otherCount = Offer::query()->where('type', $otherType)->count();

        $sort = check($request->input('sort', 'rating'));

        switch ($sort) {
            case 'time':
                $order = 'created_at';
                break;
            case 'status':
                $order = 'status';
                break;
            case 'comments':
                $order = 'count_comments';
                break;
            default:
                $order = 'rating';
        }

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
     *
     * @param int $id
     *
     * @return string
     */
    public function view(int $id): string
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
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     *
     * @return string
     */
    public function edit(int $id, Request $request, Validator $validator): string
    {
        $offer = Offer::query()->where('id', $id)->first();

        if (! $offer) {
            abort(404, __('main.record_not_found'));
        }

        if ($request->isMethod('post')) {
            $title  = $request->input('title');
            $text   = $request->input('text');
            $type   = $request->input('type');
            $closed = empty($request->input('closed')) ? 0 : 1;

            $validator->equal($request->input('token'), $_SESSION['token'], __('validator.token'))
                ->length($title, 3, 50, ['title' => __('validator.text')])
                ->length($text, 5, 1000, ['text' => __('validator.text')])
                ->in($type, Offer::TYPES, ['type' => __('offers.type_invalid')]);

            if ($validator->isValid()) {
                $title = antimat($title);
                $text  = antimat($text);

                $offer->update([
                    'type'       => $type,
                    'title'      => $title,
                    'text'       => $text,
                    'closed'     => $closed,
                    'updated_at' => SITETIME,
                ]);

                setFlash('success', __('main.record_changed_success'));
                redirect('/admin/offers/' . $offer->id);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/offers/edit', compact('offer'));
    }

    /**
     * Ответ на предложение
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     *
     * @return string
     */
    public function reply(int $id, Request $request, Validator $validator): string
    {
        $offer = Offer::query()->where('id', $id)->first();

        if (! $offer) {
            abort(404, __('main.record_not_found'));
        }

        if ($request->isMethod('post')) {
            $reply  = $request->input('reply');
            $status = $request->input('status');
            $closed = empty($request->input('closed')) ? 0 : 1;

            $validator->equal($request->input('token'), $_SESSION['token'], __('validator.token'))
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
                redirect('/admin/offers/' . $offer->id);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $statuses = Offer::STATUSES;

        return view('admin/offers/reply', compact('offer', 'statuses'));
    }

    /**
     * Пересчет комментариев
     *
     * @param Request $request
     *
     * @return void
     */
    public function restatement(Request $request): void
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, __('errors.forbidden'));
        }

        if ($request->input('token') === $_SESSION['token']) {
            restatement('offers');

            setFlash('success', __('main.success_recounted'));
        } else {
            setFlash('danger', __('validator.token'));
        }

        redirect('/admin/offers');
    }

    /**
     * Удаление записей
     *
     * @param Request   $request
     * @param Validator $validator
     *
     * @return void
     */
    public function delete(Request $request, Validator $validator): void
    {
        $page = int($request->input('page', 1));
        $del  = intar($request->input('del'));
        $type = $request->input('type') === Offer::OFFER ? Offer::OFFER : Offer::ISSUE;

        $validator->equal($request->input('token'), $_SESSION['token'], __('validator.token'))
            ->true($del, __('validator.deletion'));

        if ($validator->isValid()) {
            Offer::query()->whereIn('id', $del)->delete();

            Polling::query()
                ->where('relate_type', Offer::$morphName)
                ->whereIn('relate_id', $del)
                ->delete();

            Comment::query()
                ->where('relate_type', Offer::$morphName)
                ->whereIn('relate_id', $del)
                ->delete();

            setFlash('success', __('main.records_deleted_success'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/offers/' . $type . '?page=' . $page);
    }
}
