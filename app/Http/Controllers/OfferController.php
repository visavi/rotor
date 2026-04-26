<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Classes\Validator;
use App\Models\Flood;
use App\Models\Offer;
use App\Traits\CommentableTrait;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OfferController extends Controller
{
    use CommentableTrait;

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
    public function view(int $id, Request $request): View|RedirectResponse
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

        if ($redirect = $this->cidRedirect($offer, $request)) {
            return $redirect;
        }

        ['comments' => $comments, 'files' => $files] = $this->getCommentsData($offer);

        return view('offers/view', compact('offer', 'comments', 'files'));
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
}
