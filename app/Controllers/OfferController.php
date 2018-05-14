<?php

namespace App\Controllers;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\Comment;
use App\Models\Flood;
use App\Models\Offer;
use App\Models\Polling;
use Illuminate\Database\Query\JoinClause;

class OfferController extends BaseController
{
    /**
     * Главная страница
     */
    public function index($type = 'offer')
    {
        $otherType = $type === Offer::OFFER ? Offer::ISSUE : Offer::OFFER;

        $sort = check(Request::input('sort'));

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
     */
    public function view($id)
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
     */
    public function create()
    {
        if (! $user = getUser()) {
            abort(403, 'Авторизуйтесь для добавления записи!');
        }

        $type  = check(Request::input('type'));

        if (Request::isMethod('post')) {

            $token = check(Request::input('token'));
            $title = check(Request::input('title'));
            $text  = check(Request::input('text'));


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
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('offers/create', compact('type'));
    }

    /**
     * Редактирование записи
     */
    public function edit($id)
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

        if (! in_array($offer->status, ['wait', 'process'])) {
            abort('default', 'Данное предложение или проблема уже решена или закрыта!');
        }

        if (Request::isMethod('post')) {

            $token = check(Request::input('token'));
            $title = check(Request::input('title'));
            $text  = check(Request::input('text'));
            $type  = check(Request::input('type'));

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
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('offers/edit', compact('offer'));
    }

    /**
     * Комментарии
     */
    public function comments($id)
    {
        $offer = Offer::query()->find($id);

        if (! $offer) {
            abort(404, 'Данного предложения или проблемы не существует!');
        }

        if (Request::isMethod('post')) {

            $token = check(Request::input('token'));
            $msg   = check(Request::input('msg'));

            $validator = new Validator();
            $validator
                ->true(getUser(), 'Для добавления комментария необходимо авторизоваться!')
                ->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->length($msg, 5, 1000, ['msg' => 'Слишком длинное или короткий комментарий!'])
                ->true(Flood::isFlood(), ['msg' => 'Антифлуд! Разрешается отправлять комментарии раз в ' . Flood::getPeriod() . ' секунд!'])
                ->empty($offer->closed, ['msg' => 'Комментирование данной записи закрыто!']);

            if ($validator->isValid()) {

                $msg = antimat($msg);

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
                setInput(Request::all());
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
     */
    public function editComment($id, $cid)
    {
        $page = int(Request::input('page', 1));
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

        if (Request::isMethod('post')) {
            $token = check(Request::input('token'));
            $msg   = check(Request::input('msg'));
            $page  = int(Request::input('page', 1));

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
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('offers/editcomment', compact('offer', 'comment', 'page'));
    }

    /**
     * Переадресация на последнюю страницу
     */
    public function end($id)
    {
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
     */
    public function viewComment($id, $cid)
    {
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
