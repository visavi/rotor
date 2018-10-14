<?php

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
            abort(403, 'Доступ запрещен!');
        }
    }

    /**
     * Главная страница
     *
     * @param string $type
     * @return string
     */
    public function index($type = Offer::OFFER): string
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

        return view('admin/offers/index', compact('offers', 'page', 'order', 'type'));
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
            ->where('offers.id', $id)
            ->first();

        if (! $offer) {
            abort(404, 'Данного предложения или проблемы не существует!');
        }

        return view('admin/offers/view', compact('offer'));
    }

    /**
     * Редактирование записи
     *
     * @param int $id
     * @return string
     */
    public function edit(int $id): string
    {
        $offer = Offer::query()->where('id', $id)->first();

        if (! $offer) {
            abort(404, 'Данного предложения или проблемы не существует!');
        }

        if ($request->isMethod('post')) {

            $token  = check($request->input('token'));
            $title  = check($request->input('title'));
            $text   = check($request->input('text'));
            $type   = check($request->input('type'));
            $closed = empty($request->input('closed')) ? 0 : 1;

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
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/offers/edit', compact('offer'));
    }

    /**
     * Ответ на предложение
     *
     * @param int $id
     * @return string
     */
    public function reply(int $id): string
    {
        $offer = Offer::query()->where('id', $id)->first();

        if (! $offer) {
            abort(404, 'Данного предложения или проблемы не существует!');
        }

        if ($request->isMethod('post')) {

            $token  = check($request->input('token'));
            $reply  = check($request->input('reply'));
            $status = check($request->input('status'));
            $closed = empty($request->input('closed')) ? 0 : 1;

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
     * @return void
     */
    public function restatement(): void
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, 'Доступ запрещен!');
        }

        $token = check($request->input('token'));

        if ($token === $_SESSION['token']) {

            restatement('offers');

            setFlash('success', 'Комментарии успешно пересчитаны!');
        } else {
            setFlash('danger', 'Ошибка! Неверный идентификатор сессии, повторите действие!');
        }

        redirect('/admin/offers');
    }

    /**
     * Удаление записей
     *
     * @return void
     */
    public function delete(): void
    {
        $page  = int($request->input('page', 1));
        $token = check($request->input('token'));
        $del   = intar($request->input('del'));
        $type  = $request->input('type') === Offer::OFFER ? Offer::OFFER : Offer::ISSUE;

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
