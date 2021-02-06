<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Classes\Validator;
use App\Models\Comment;
use App\Models\Flood;
use App\Models\Offer;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;

class OfferController extends BaseController
{
    /**
     * Главная страница
     *
     * @param Request $request
     * @param string  $type
     *
     * @return string
     */
    public function index(Request $request, string $type = 'offer'): string
    {
        $offerCount = Offer::query()->where('type', Offer::OFFER)->count();
        $issueCount = Offer::query()->where('type', Offer::ISSUE)->count();

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
            ->appends(compact('type', 'sort'));

        return view('offers/index', compact('offers', 'order', 'type', 'sort', 'offerCount', 'issueCount'));
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
            ->select('offers.*', 'pollings.vote')
            ->where('offers.id', $id)
            ->leftJoin('pollings', static function (JoinClause $join) {
                $join->on('offers.id', 'pollings.relate_id')
                    ->where('pollings.relate_type', Offer::$morphName)
                    ->where('pollings.user_id', getUser('id'));
            })
            ->first();

        if (! $offer) {
            abort(404, __('main.record_not_found'));
        }

        return view('offers/view', compact('offer'));
    }

    /**
     * Создание записи
     *
     * @param Request   $request
     * @param Validator $validator
     * @param Flood     $flood
     *
     * @return string
     */
    public function create(Request $request, Validator $validator, Flood $flood): string
    {
        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        $type = $request->input('type');

        if ($request->isMethod('post')) {
            $title = $request->input('title');
            $text  = $request->input('text');

            $validator->equal($request->input('token'), $_SESSION['token'], __('validator.token'))
                ->length($title, 5, 50, ['title' => __('validator.text')])
                ->length($text, 5, 1000, ['text' => __('validator.text')])
                ->false($flood->isFlood(), ['msg' => __('validator.flood', ['sec' => $flood->getPeriod()])])
                ->in($type, Offer::TYPES, ['type' => __('offers.type_invalid')])
                ->gte(getUser('point'), setting('addofferspoint'), __('offers.condition_add', ['point' => plural(setting('addofferspoint'), setting('scorename'))]));

            if ($validator->isValid()) {
                $title = antimat($title);
                $text  = antimat($text);

                /** @var Offer $offer */
                $offer = Offer::query()->create([
                    'type'       => $type,
                    'title'      => $title,
                    'text'       => $text,
                    'user_id'    => $user->id,
                    'rating'     => 1,
                    'status'     => 'wait',
                    'created_at' => SITETIME,
                ]);

                $flood->saveState();

                setFlash('success', __('main.record_added_success'));
                redirect('/offers/' . $offer->id);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('offers/create', compact('type'));
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
        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        $offer = Offer::query()
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (! $offer) {
            abort(404, __('main.record_not_found'));
        }

        if (! in_array($offer->status, ['wait', 'process'])) {
            abort('default', __('offers.already_resolved'));
        }

        if ($request->isMethod('post')) {
            $title = $request->input('title');
            $text  = $request->input('text');
            $type  = $request->input('type');

            $validator->equal($request->input('token'), $_SESSION['token'], __('validator.token'))
                ->length($title, 5, 50, ['title' => __('validator.text')])
                ->length($text, 5, 1000, ['text' => __('validator.text')])
                ->in($type, Offer::TYPES, ['type' => __('offers.type_invalid')]);

            if ($validator->isValid()) {
                $title = antimat($title);
                $text  = antimat($text);

                $offer->update([
                    'type'       => $type,
                    'title'      => $title,
                    'text'       => $text,
                    'updated_at' => SITETIME,
                ]);

                setFlash('success', __('main.record_changed_success'));
                redirect('/offers/' . $offer->id);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('offers/edit', compact('offer'));
    }

    /**
     * Комментарии
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     * @param Flood     $flood
     *
     * @return string
     */
    public function comments(int $id, Request $request, Validator $validator, Flood $flood): string
    {
        /** @var Offer $offer */
        $offer = Offer::query()->find($id);

        if (! $offer) {
            abort(404, __('main.record_not_found'));
        }

        if ($request->isMethod('post')) {
            $msg = $request->input('msg');

            $validator
                ->true(getUser(), __('main.not_authorized'))
                ->equal($request->input('token'), $_SESSION['token'], __('validator.token'))
                ->length($msg, 5, setting('comment_length'), ['msg' => __('validator.text')])
                ->false($flood->isFlood(), ['msg' => __('validator.flood', ['sec' => $flood->getPeriod()])])
                ->empty($offer->closed, ['msg' => __('offers.offer_closed')]);

            if ($validator->isValid()) {
                $msg = antimat($msg);

                /** @var Comment $comment */
                $comment = $offer->comments()->create([
                    'text'        => $msg,
                    'user_id'     => getUser('id'),
                    'created_at'  => SITETIME,
                    'ip'          => getIp(),
                    'brow'        => getBrowser(),
                ]);

                $user = getUser();
                $user->increment('allcomments');
                $user->increment('point');
                $user->increment('money', 5);

                $offer->increment('count_comments');

                $flood->saveState();
                sendNotify($msg, '/offers/comment/' . $offer->id . '/' . $comment->id, $offer->title);

                setFlash('success', __('main.comment_added_success'));
                redirect('/offers/end/' . $offer->id);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $comments = $offer->comments()
            ->orderBy('created_at')
            ->paginate(setting('comments_per_page'));

        return view('offers/comments', compact('offer', 'comments'));
    }

    /**
     * Подготовка к редактированию комментария
     *
     * @param int       $id
     * @param int       $cid
     * @param Request   $request
     * @param Validator $validator
     *
     * @return string
     */
    public function editComment(int $id, int $cid, Request $request, Validator $validator): string
    {
        $page = int($request->input('page', 1));

        /** @var Offer $offer */
        $offer = Offer::query()->find($id);

        if (! $offer) {
            abort(404, __('main.record_not_found'));
        }

        if (! getUser()) {
            abort(403, __('main.not_authorized'));
        }

        $comment = $offer->comments()
            ->where('id', $cid)
            ->where('user_id', getUser('id'))
            ->first();

        if (! $comment) {
            abort('default', __('main.comment_deleted'));
        }

        if ($comment->created_at + 600 < SITETIME) {
            abort('default', __('main.editing_impossible'));
        }

        if ($request->isMethod('post')) {
            $msg  = $request->input('msg');
            $page = int($request->input('page', 1));

            $validator
                ->equal($request->input('token'), $_SESSION['token'], __('validator.token'))
                ->length($msg, 5, setting('comment_length'), ['msg' => __('validator.text')]);

            if ($validator->isValid()) {
                $msg = antimat($msg);

                $comment->update([
                    'text' => $msg,
                ]);

                setFlash('success', __('main.comment_edited_success'));
                redirect('/offers/comments/' . $offer->id . '?page=' . $page);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('offers/editcomment', compact('offer', 'comment', 'page'));
    }

    /**
     * Переадресация на последнюю страницу
     *
     * @param int $id
     */
    public function end(int $id): void
    {
        /** @var Offer $offer */
        $offer = Offer::query()->find($id);

        if (! $offer) {
            abort(404, __('main.record_not_found'));
        }

        $total = $offer->comments()->count();

        $end = ceil($total / setting('comments_per_page'));
        redirect('/offers/comments/' . $offer->id . '?page=' . $end);
    }

    /**
     * Переход к сообщению
     *
     * @param int $id
     * @param int $cid
     */
    public function viewComment(int $id, int $cid): void
    {
        /** @var Offer $offer */
        $offer = Offer::query()->find($id);

        if (! $offer) {
            abort(404, __('main.record_not_found'));
        }

        $total = $offer->comments()
            ->where('id', '<=', $cid)
            ->orderBy('created_at')
            ->count();

        $end = ceil($total / setting('comments_per_page'));
        redirect('/offers/comments/' . $offer->id . '?page=' . $end . '#comment_' . $cid);
    }
}
