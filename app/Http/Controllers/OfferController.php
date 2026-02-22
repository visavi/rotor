<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Classes\Validator;
use App\Models\Comment;
use App\Models\Flood;
use App\Models\Offer;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OfferController extends Controller
{
    /**
     * Главная страница
     */
    public function index(Request $request, string $type = 'offer'): View
    {
        $offerCount = Offer::query()->where('type', Offer::OFFER)->count();
        $issueCount = Offer::query()->where('type', Offer::ISSUE)->count();

        $sort = $request->input('sort', 'date');
        $order = $request->input('order', 'desc');

        [$sorting, $orderBy] = Offer::getSorting($sort, $order);

        $offers = Offer::query()
            ->where('type', $type)
            ->orderBy(...$orderBy)
            ->with('user', 'poll')
            ->paginate(setting('postoffers'))
            ->appends(compact('type', 'sort', 'order'));

        return view('offers/index', compact('offers', 'order', 'type', 'sort', 'sorting', 'offerCount', 'issueCount'));
    }

    /**
     * Просмотр записи
     */
    public function view(int $id): View
    {
        $offer = Offer::query()
            ->select('offers.*', 'polls.vote')
            ->where('offers.id', $id)
            ->leftJoin('polls', static function (JoinClause $join) {
                $join->on('offers.id', 'polls.relate_id')
                    ->where('polls.relate_type', Offer::$morphName)
                    ->where('polls.user_id', getUser('id'));
            })
            ->first();

        if (! $offer) {
            abort(404, __('main.record_not_found'));
        }

        return view('offers/view', compact('offer'));
    }

    /**
     * Создание записи
     */
    public function create(Request $request, Validator $validator, Flood $flood): View|RedirectResponse
    {
        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        $type = $request->input('type');

        if ($request->isMethod('post')) {
            $title = $request->input('title');
            $text = $request->input('text');

            $validator
                ->length($title, setting('offer_title_min'), setting('offer_title_max'), ['title' => __('validator.text')])
                ->length($text, setting('offer_text_min'), setting('offer_text_max'), ['text' => __('validator.text')])
                ->false($flood->isFlood(), ['msg' => __('validator.flood', ['sec' => $flood->getPeriod()])])
                ->in($type, Offer::TYPES, ['type' => __('offers.type_invalid')])
                ->gte(getUser('point'), setting('addofferspoint'), __('offers.condition_add', ['point' => plural(setting('addofferspoint'), setting('scorename'))]));

            if ($validator->isValid()) {
                $title = antimat($title);
                $text = antimat($text);

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

                return redirect()->route('offers.view', ['id' => $offer->id]);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return view('offers/create', compact('type'));
    }

    /**
     * Редактирование записи
     */
    public function edit(int $id, Request $request, Validator $validator): View|RedirectResponse
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
            abort(200, __('offers.already_resolved'));
        }

        if ($request->isMethod('post')) {
            $title = $request->input('title');
            $text = $request->input('text');
            $type = $request->input('type');

            $validator
                ->length($title, setting('offer_title_min'), setting('offer_title_max'), ['title' => __('validator.text')])
                ->length($text, setting('offer_text_min'), setting('offer_text_max'), ['text' => __('validator.text')])
                ->in($type, Offer::TYPES, ['type' => __('offers.type_invalid')]);

            if ($validator->isValid()) {
                $title = antimat($title);
                $text = antimat($text);

                $offer->update([
                    'type'       => $type,
                    'title'      => $title,
                    'text'       => $text,
                    'updated_at' => SITETIME,
                ]);

                setFlash('success', __('main.record_changed_success'));

                return redirect()->route('offers.view', ['id' => $offer->id]);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return view('offers/edit', compact('offer'));
    }

    /**
     * Комментарии
     */
    public function comments(int $id, Request $request, Validator $validator, Flood $flood): View|RedirectResponse
    {
        $offer = Offer::query()->find($id);

        if (! $offer) {
            abort(404, __('main.record_not_found'));
        }

        $cid = int($request->input('cid'));
        if ($cid) {
            $total = $offer->comments->where('id', '<=', $cid)->count();

            $page = ceil($total / setting('comments_per_page'));
            $page = $page > 1 ? $page : null;

            return redirect()->route('offers.comments', ['id' => $offer->id, 'page' => $page])
                ->withFragment('comment_' . $cid);
        }

        if ($request->isMethod('post')) {
            $msg = $request->input('msg');

            $validator
                ->true(getUser(), __('main.not_authorized'))
                ->length($msg, setting('comment_text_min'), setting('comment_text_max'), ['msg' => __('validator.text')])
                ->false($flood->isFlood(), ['msg' => __('validator.flood', ['sec' => $flood->getPeriod()])])
                ->empty($offer->closed, ['msg' => __('offers.offer_closed')]);

            if ($validator->isValid()) {
                $msg = antimat($msg);

                $comment = $offer->comments()->create([
                    'text'       => $msg,
                    'user_id'    => getUser('id'),
                    'created_at' => SITETIME,
                    'ip'         => getIp(),
                    'brow'       => getBrowser(),
                ]);

                $user = getUser();
                $user->increment('allcomments');
                $user->increment('point', setting('comment_point'));
                $user->increment('money', setting('comment_money'));

                $offer->increment('count_comments');

                $flood->saveState();
                sendNotify($msg, route('offers.comments', ['id' => $offer->id, 'cid' => $comment->id], false), $offer->title);

                setFlash('success', __('main.comment_added_success'));

                $page = ceil($offer->count_comments / setting('comments_per_page'));
                $page = $page > 1 ? $page : null;

                return redirect()->route('offers.comments', ['id' => $offer->id, 'page' => $page]);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        $comments = $offer->comments()
            ->select('comments.*', 'polls.vote')
            ->leftJoin('polls', static function (JoinClause $join) {
                $join->on('comments.id', 'polls.relate_id')
                    ->where('polls.relate_type', Comment::$morphName)
                    ->where('polls.user_id', getUser('id'));
            })
            ->orderBy('created_at')
            ->paginate(setting('comments_per_page'));

        return view('offers/comments', compact('offer', 'comments'));
    }

    /**
     * Подготовка к редактированию комментария
     */
    public function editComment(int $id, int $cid, Request $request, Validator $validator): View|RedirectResponse
    {
        $page = int($request->input('page', 1));

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
            abort(200, __('main.comment_deleted'));
        }

        if ($comment->created_at + 600 < SITETIME) {
            abort(200, __('main.editing_impossible'));
        }

        if ($request->isMethod('post')) {
            $msg = $request->input('msg');
            $page = int($request->input('page', 1));

            $validator->length($msg, setting('comment_text_min'), setting('comment_text_max'), ['msg' => __('validator.text')]);

            if ($validator->isValid()) {
                $msg = antimat($msg);

                $comment->update([
                    'text' => $msg,
                ]);

                setFlash('success', __('main.comment_edited_success'));

                return redirect()->route('offers.comments', ['id' => $offer->id, 'page' => $page]);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return view('offers/editcomment', compact('offer', 'comment', 'page'));
    }
}
