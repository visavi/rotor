<?php

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
     * @param string  $type
     * @param Request $request
     * @return string
     */
    public function index(Request $request, $type = 'offer'): string
    {
        $otherType = $type === Offer::OFFER ? Offer::ISSUE : Offer::OFFER;

        $sort = check($request->input('sort'));

        $total = Offer::query()->where('type', $type)->count();
        $page = paginate(setting('postoffers'), $total);

        $page->otherTotal = Offer::query()->where('type', $otherType)->count();

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
            ->orderBy($order, 'desc')
            ->offset($page->offset)
            ->limit($page->limit)
            ->with('user')
            ->get();

        return view('offers/index', compact('offers', 'page', 'order', 'type'));
    }

    /**
     * Просмотр записи
     *
     * @param int $id
     * @return string
     */
    public function view(int $id): string
    {
        $offer = Offer::query()
            ->select('offers.*', 'pollings.vote')
            ->where('offers.id', $id)
            ->leftJoin('pollings', function (JoinClause $join) {
                $join->on('offers.id', '=', 'pollings.relate_id')
                    ->where('pollings.relate_type', Offer::class)
                    ->where('pollings.user_id', getUser('id'));
            })
            ->first();

        if (! $offer) {
            abort(404, 'Данного предложения или проблемы не существует!');
        }

        return view('offers/view', compact('offer'));
    }

    /**
     * Создание записи
     *
     * @param Request $request
     * @return string
     */
    public function create(Request $request): string
    {
        if (! $user = getUser()) {
            abort(403, 'Авторизуйтесь для добавления записи!');
        }

        $type  = check($request->input('type'));

        if ($request->isMethod('post')) {

            $token = check($request->input('token'));
            $title = check($request->input('title'));
            $text  = check($request->input('text'));


            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], ['Неверный идентификатор сессии, повторите действие!'])
                ->length($title, 5, 50, ['title' => 'Слишком длинный или короткий заголовок!'])
                ->length($text, 5, 1000, ['text' => 'Слишком длинное или короткое описание!'])
                ->true(Flood::isFlood(), ['text' => 'Антифлуд! Разрешается добавлять записи раз в ' . Flood::getPeriod() . ' секунд!'])
                ->in($type, array_keys(Offer::TYPES), ['type' => 'Выбран неверный тип записи! (Необходимо предложение или проблема)'])
                ->gte(getUser('point'), setting('addofferspoint'), ['Для добавления предложения или проблемы вам необходимо набрать ' . plural(setting('addofferspoint'), setting('scorename')) . '!']);

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

                setFlash('success', 'Запись успешно добавлена!');
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
     * @param int     $id
     * @param Request $request
     * @return string
     */
    public function edit(int $id, Request $request): string
    {
        if (! $user = getUser()) {
            abort(403, 'Авторизуйтесь для редактирования записи!');
        }

        $offer = Offer::query()
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (! $offer) {
            abort(404, 'Данного предложения или проблемы не существует!');
        }

        if (! \in_array($offer->status, ['wait', 'process'])) {
            abort('default', 'Данное предложение или проблема уже решена или закрыта!');
        }

        if ($request->isMethod('post')) {

            $token = check($request->input('token'));
            $title = check($request->input('title'));
            $text  = check($request->input('text'));
            $type  = check($request->input('type'));

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], ['Неверный идентификатор сессии, повторите действие!'])
                ->length($title, 5, 50, ['title' => 'Слишком длинный или короткий заголовок!'])
                ->length($text, 5, 1000, ['text' => 'Слишком длинное или короткое описание!'])
                ->in($type, array_keys(Offer::TYPES), ['type' => 'Выбран неверный тип записи! (Необходимо предложение или проблема)']);

            if ($validator->isValid()) {

                $title = antimat($title);
                $text  = antimat($text);

                $offer->update([
                    'type'       => $type,
                    'title'      => $title,
                    'text'       => $text,
                    'updated_at' => SITETIME,
                ]);

                setFlash('success', 'Запись успешно изменена!');
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
     * @param int     $id
     * @param Request $request
     * @return string
     */
    public function comments(int $id, Request $request): string
    {
        /** @var Offer $offer */
        $offer = Offer::query()->find($id);

        if (! $offer) {
            abort(404, 'Данного предложения или проблемы не существует!');
        }

        if ($request->isMethod('post')) {

            $token = check($request->input('token'));
            $msg   = check($request->input('msg'));

            $validator = new Validator();
            $validator
                ->true(getUser(), 'Для добавления комментария необходимо авторизоваться!')
                ->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->length($msg, 5, 1000, ['msg' => 'Слишком длинное или короткий комментарий!'])
                ->true(Flood::isFlood(), ['msg' => 'Антифлуд! Разрешается отправлять комментарии раз в ' . Flood::getPeriod() . ' секунд!'])
                ->empty($offer->closed, ['msg' => 'Комментирование данной записи закрыто!']);

            if ($validator->isValid()) {

                $msg = antimat($msg);

                /** @var Comment $comment */
                $comment = Comment::query()->create([
                    'relate_type' => Offer::class,
                    'relate_id'   => $offer->id,
                    'text'        => $msg,
                    'user_id'     => getUser('id'),
                    'created_at'  => SITETIME,
                    'ip'          => getIp(),
                    'brow'        => getBrowser(),
                ]);

                $offer->increment('count_comments');

                sendNotify($msg, '/offers/comment/' . $offer->id . '/' . $comment->id, $offer->title);

                setFlash('success', 'Комментарий успешно добавлен!');
                redirect('/offers/end/' . $offer->id);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $total = Comment::query()
            ->where('relate_type', Offer::class)
            ->where('relate_id', $id)
            ->count();

        $page = paginate(setting('postcommoffers'), $total);

        $comments = Comment::query()
            ->where('relate_type', Offer::class)
            ->where('relate_id', $id)
            ->orderBy('created_at')
            ->offset($page->offset)
            ->limit($page->limit)
            ->get();

        return view('offers/comments', compact('offer', 'comments', 'page'));
    }

    /**
     * Подготовка к редактированию комментария
     *
     * @param int     $id
     * @param int     $cid
     * @param Request $request
     * @return string
     */
    public function editComment(int $id, int $cid, Request $request): string
    {
        $page = int($request->input('page', 1));

        /** @var Offer $offer */
        $offer = Offer::query()->find($id);

        if (! $offer) {
            abort(404, 'Данного предложения или проблемы не существует!');
        }

        if (! getUser()) {
            abort(403, 'Для редактирования комментариев небходимо авторизоваться!');
        }

        $comment = Comment::query()
            ->where('relate_type', Offer::class)
            ->where('id', $cid)
            ->where('user_id', getUser('id'))
            ->first();

        if (! $comment) {
            abort('default', 'Комментарий удален или вы не автор этого комментария!');
        }

        if ($comment->created_at + 600 < SITETIME) {
            abort('default', 'Редактирование невозможно, прошло более 10 минут!');
        }

        if ($request->isMethod('post')) {
            $token = check($request->input('token'));
            $msg   = check($request->input('msg'));
            $page  = int($request->input('page', 1));

            $validator = new Validator();
            $validator
                ->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->length($msg, 5, 1000, ['msg' => 'Слишком длинный или короткий комментарий!']);

            if ($validator->isValid()) {
                $msg = antimat($msg);

                $comment->update([
                    'text' => $msg,
                ]);

                setFlash('success', 'Комментарий успешно отредактирован!');
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
            abort(404, 'Данного предложения или проблемы не существует!');
        }

        $total = Comment::query()
            ->where('relate_type', Offer::class)
            ->where('relate_id', $offer->id)
            ->count();

        $end = ceil($total / setting('postcommoffers'));
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
            abort(404, 'Данного предложения или проблемы не существует!');
        }

        $total = Comment::query()
            ->where('relate_type', Offer::class)
            ->where('relate_id', $id)
            ->where('id', '<=', $cid)
            ->orderBy('created_at')
            ->count();

        $end = ceil($total / setting('postcommoffers'));
        redirect('/offers/comments/' . $offer->id . '?page=' . $end . '#comment_' . $cid);
    }
}
