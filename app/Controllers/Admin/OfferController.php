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
            abort(403, trans('errors.forbidden'));
        }
    }

    /**
     * Главная страница
     *
     * @param string  $type
     * @param Request $request
     * @return string
     */
    public function index(Request $request, $type = Offer::OFFER): string
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
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     * @return string
     */
    public function edit(int $id, Request $request, Validator $validator): string
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

            $validator->equal($token, $_SESSION['token'], trans('validator.token'))
                ->length($title, 5, 50, ['title' => trans('validator.title')])
                ->length($text, 5, 1000, ['text' => trans('validator.text')])
                ->in($type, Offer::TYPES, ['type' => 'Выбран неверный тип записи! (Необходимо предложение или проблема)']);

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
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     * @return string
     */
    public function reply(int $id, Request $request, Validator $validator): string
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

            $validator->equal($token, $_SESSION['token'], trans('validator.token'))
                ->length($reply, 5, 3000, ['reply' => trans('validator.text')])
                ->in($status, Offer::STATUSES, ['status' => 'Недопустимый статус предложения или проблемы!']);

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
     * @param Request $request
     * @return void
     */
    public function restatement(Request $request): void
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, trans('errors.forbidden'));
        }

        $token = check($request->input('token'));

        if ($token === $_SESSION['token']) {

            restatement('offers');

            setFlash('success', 'Комментарии успешно пересчитаны!');
        } else {
            setFlash('danger', trans('validator.token'));
        }

        redirect('/admin/offers');
    }

    /**
     * Удаление записей
     *
     * @param Request   $request
     * @param Validator $validator
     * @return void
     */
    public function delete(Request $request, Validator $validator): void
    {
        $page  = int($request->input('page', 1));
        $token = check($request->input('token'));
        $del   = intar($request->input('del'));
        $type  = $request->input('type') === Offer::OFFER ? Offer::OFFER : Offer::ISSUE;

        $validator->equal($token, $_SESSION['token'], trans('validator.token'))
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
