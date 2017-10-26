<?php

namespace App\Controllers;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\Comment;
use App\Models\Flood;
use App\Models\Offer;
use App\Models\Polling;

class OfferController extends BaseController
{
    /**
     * Главная страница
     */
    public function index($type = 'offer')
    {
        $otherType = $type == 'offer' ? 'issue' : 'offer';

        $sort = check(Request::input('sort', 'rating'));

        $total = Offer::query()->where('type', $type)->count();
        $page = paginate(setting('postoffers'), $total);

        $page['otherTotal'] = Offer::query()->where('type', $otherType)->count();

        switch ($sort) {
            case 'times':
                $order = 'created_at';
                break;
            case 'status':
                $order = 'status';
                break;
            case 'comments':
                $order = 'comments';
                break;
            default:
                $order = 'rating';
        }

        $offers = Offer::query()
            ->where('type', $type)
            ->orderBy($order, 'desc')
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->with('user')
            ->get();

        return view('offer/index', compact('offers', 'page', 'sort', 'type'));
    }

    /**
     * Просмотр записи
     */
    public function view($id)
    {
        $offer = Offer::query()
            ->select('offers.*', 'pollings.vote')
            ->where('offers.id', $id)
            ->leftJoin('pollings', function ($join) {
                $join->on('offers.id', '=', 'pollings.relate_id')
                    ->where('pollings.relate_type', Offer::class)
                    ->where('pollings.user_id', getUser('id'));
            })
            ->first();

        if (! $offer) {
            abort(404, 'Данного предложения или проблемы не существует!');
        }

        return view('offer/view', compact('offer'));
    }

    /**
     * Создание записи
     */
    public function create()
    {
        if (! $user = getUser()) {
            abort(403, 'Авторизуйтесь для добавления записи!');
        }

        if (Request::isMethod('post')) {

            $token = check(Request::input('token'));
            $title = check(Request::input('title'));
            $text  = check(Request::input('text'));
            $type  = check(Request::input('type'));

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], ['Неверный идентификатор сессии, повторите действие!'])
                ->length($title, 5, 50, ['title' => 'Ошибка! Слишком длинный или короткий заголовок!'])
                ->length($text, 5, 1000, ['text' => 'Ошибка! Слишком длинное или короткое описание!'])
                ->true(Flood::isFlood(), ['text' => 'Антифлуд! Разрешается добавлять записи раз в ' . Flood::getPeriod() . ' секунд!'])
                ->in($type, ['offer', 'issue'], ['type' => 'Выбран неверный тип записи! (Необходимо предложение или проблема)'])
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

        return view('offer/create');
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
                ->length($title, 5, 50, ['title' => 'Ошибка! Слишком длинный или короткий заголовок!'])
                ->length($text, 5, 1000, ['text' => 'Ошибка! Слишком длинное или короткое описание!'])
                ->true(Flood::isFlood(), ['text' => 'Антифлуд! Разрешается добавлять записи раз в ' . Flood::getPeriod() . ' секунд!'])
                ->in($type, ['offer', 'issue'], ['type' => 'Выбран неверный тип записи! (Необходимо предложение или проблема)'])
                ->gte(getUser('point'), setting('addofferspoint'), ['Для добавления предложения или проблемы вам необходимо набрать ' . plural(setting('addofferspoint'), setting('scorename')) . '!']);

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

        return view('offer/edit', compact('offer'));
    }

    /**
     * Комментарии
     */
    public function comments($id)
    {
        $offer = Offer::query()->find($id);

        if (! $offer) {
            abort('default', 'Данного предложения или проблемы не существует!');
        }

        if (Request::isMethod('post')) {

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
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->get();

        return view('offer/comments', compact('offer', 'comments', 'page'));
    }
}
