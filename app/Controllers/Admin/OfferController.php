<?php

namespace App\Controllers\Admin;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\Comment;
use App\Models\Offer;
use App\Models\Polling;
use App\Models\User;

class OfferController extends AdminController
{
    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();

        if (! isAdmin(User::ADMIN)) {
            abort(403, 'Доступ запрещен!');
        }
    }

    /**
     * Главная страница
     */
    public function index($type = Offer::OFFER)
    {
        $otherType = $type == Offer::OFFER ? Offer::ISSUE : Offer::OFFER;

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

        return view('admin/offers/index', compact('offers', 'page', 'order', 'type'));
    }

    /**
     * Просмотр записи
     */
    public function view($id)
    {
        $offer = Offer::query()
            ->where('offers.id', $id)
            ->first();

        if (! $offer) {
            abort(404, 'Данного предложения или проблемы не существует!');
        }

        return view('admin/offers/view', compact('offer'));
    }

    /**
     * Редактирование записи
     */
    public function edit($id)
    {
        $offer = Offer::query()->where('id', $id)->first();

        if (! $offer) {
            abort(404, 'Данного предложения или проблемы не существует!');
        }

        if (Request::isMethod('post')) {

            $token  = check(Request::input('token'));
            $title  = check(Request::input('title'));
            $text   = check(Request::input('text'));
            $type   = check(Request::input('type'));
            $closed = empty(Request::input('closed')) ? 0 : 1;

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
                    'closed'     => $closed,
                    'updated_at' => SITETIME,
                ]);

                setFlash('success', 'Запись успешно изменена!');
                redirect('/admin/offers/' . $offer->id);
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/offers/edit', compact('offer'));
    }

    /**
     * Ответ на предложение
     */
    public function reply($id)
    {
        $offer = Offer::query()->where('id', $id)->first();

        if (! $offer) {
            abort(404, 'Данного предложения или проблемы не существует!');
        }

        if (Request::isMethod('post')) {

            $token  = check(Request::input('token'));
            $reply  = check(Request::input('reply'));
            $status = check(Request::input('status'));
            $closed = empty(Request::input('closed')) ? 0 : 1;

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], ['Неверный идентификатор сессии, повторите действие!'])
                ->length($reply, 5, 3000, ['reply' => 'Слишком длинный или короткий текст ответа!'])
                ->in($status, array_keys(Offer::STATUSES), ['status' => 'Недопустимый статус предложения или проблемы!']);

            if ($validator->isValid()) {

                $reply = antimat($reply);

                $offer->update([
                    'reply'         => $reply,
                    'reply_user_id' => getUser('id'),
                    'status'        => $status,
                    'closed'        => $closed,
                    'updated_at'    => SITETIME,
                ]);

                setFlash('success', 'Ответ успешно добавлен!');
                redirect('/admin/offers/' . $offer->id);
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $statuses = Offer::STATUSES;

        return view('admin/offers/reply', compact('offer', 'statuses'));
    }

    /**
     * Пересчет комментариев
     */
    public function restatement()
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, 'Доступ запрещен!');
        }

        $token = check(Request::input('token'));

        if ($token == $_SESSION['token']) {

            restatement('offers');

            setFlash('success', 'Комментарии успешно пересчитаны!');
        } else {
            setFlash('danger', 'Ошибка! Неверный идентификатор сессии, повторите действие!');
        }

        redirect('/admin/offers');
    }

    /**
     * Удаление записей
     */
    public function delete()
    {
        $page  = int(Request::input('page', 1));
        $token = check(Request::input('token'));
        $del   = intar(Request::input('del'));
        $type  = Request::input('type') == Offer::OFFER ? Offer::OFFER : Offer::ISSUE;

        $validator = new Validator();
        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
            ->true($del, 'Отсутствуют выбранные записи для удаления!');

        if ($validator->isValid()) {

            Offer::query()->whereIn('id', $del)->delete();

            Polling::query()
                ->where('relate_type', Offer::class)
                ->whereIn('relate_id', $del)
                ->delete();

            Comment::query()
                ->where('relate_type', Offer::class)
                ->whereIn('relate_id', $del)
                ->delete();

            setFlash('success', 'Выбранные записи успешно удалены!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/offers/' . $type . '?page=' . $page);
    }
}
