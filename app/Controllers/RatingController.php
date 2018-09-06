<?php

namespace App\Controllers;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Database\Capsule\Manager as DB;

class RatingController extends BaseController
{
    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();

        if (! getUser()) {
            abort(403, 'Для изменения или просмотра рейтинга небходимо авторизоваться!');
        }
    }

    /**
     * Изменение рейтинга
     */
    public function index($login)
    {
        $vote = Request::input('vote');
        $user = User::query()->where('login', $login)->first();

        if (! $user) {
            abort(404, 'Данного пользователя не существует!');
        }

        if (getUser('id') == $user->id) {
            abort('default', 'Запрещено изменять репутацию самому себе!');
        }

        if (getUser('point') < setting('editratingpoint')) {
            abort('default', 'Для изменения репутации необходимо набрать '.plural(setting('editratingpoint'), setting('scorename')).'!');
        }

        // Голосовать за того же пользователя можно через 90 дней
        $getRating = Rating::query()
            ->where('user_id', getUser('id'))
            ->where('recipient_id', $user->id)
            ->where('created_at', '>', strtotime('-3 month', SITETIME))
            ->first();

        if ($getRating) {
            abort('default', 'Вы уже изменяли репутацию этому пользователю!');
        }

        if (Request::isMethod('post')) {

            $token = check(Request::input('token'));
            $text  = check(Request::input('text'));

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->length($text, 5, 250, ['text' => 'Слишком длинный или короткий комментарий!']);

            if (getUser('rating') < 10 && empty($vote)) {
                $validator->addError('Уменьшать репутацию могут только пользователи с рейтингом 10 или выше!');
            }

            if ($validator->isValid()) {

                $text = antimat($text);

                Rating::query()->create([
                    'user_id'      => getUser('id'),
                    'recipient_id' => $user->id,
                    'text'         => $text,
                    'vote'         => $vote === 'plus' ? '+' : '-',
                    'created_at'   => SITETIME,
                ]);

                if ($vote === 'plus') {
                    $text = 'Пользователь [b]' . getUser()->getProfile() . '[/b] поставил вам плюс! (Ваш рейтинг: ' . ($user['rating'] + 1) . ')' . PHP_EOL . 'Комментарий: ' . $text;

                    $user->update([
                        'rating'    => DB::raw('posrating - negrating + 1'),
                        'posrating' => DB::raw('posrating + 1'),
                    ]);

                } else {

                    $text = 'Пользователь [b]' . getUser()->getProfile() . '[/b] поставил вам минус! (Ваш рейтинг: ' . ($user['rating'] - 1) . ')' . PHP_EOL . 'Комментарий: ' . $text;

                    $user->update([
                        'rating'    => DB::raw('posrating - negrating - 1'),
                        'negrating' => DB::raw('negrating + 1'),
                    ]);
                }

                $user->sendMessage(null, $text);

                setFlash('success', 'Репутация успешно изменена!');
                redirect('/users/'.$user->login);
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('ratings/index', compact('user', 'vote'));
    }

    /**
     *  Полученные голоса
     */
    public function received($login)
    {
        $user = User::query()->where('login', $login)->first();

        if (! $user) {
            abort(404, 'Данного пользователя не существует!');
        }

        $total = Rating::query()->where('recipient_id', $user->id)->count();
        $page = paginate(setting('ratinglist'), $total);

        $ratings = Rating::query()
            ->where('recipient_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit($page->limit)
            ->offset($page->offset)
            ->with('user')
            ->get();

        return view('ratings/rathistory', compact('ratings', 'user', 'page'));
    }

    /**
     *  Отданные голоса
     */
    public function gave($login)
    {
        $user = User::query()->where('login', $login)->first();

        if (! $user) {
            abort(404, 'Данного пользователя не существует!');
        }

        $total = Rating::query()->where('user_id', $user->id)->count();
        $page = paginate(setting('ratinglist'), $total);

        $ratings = Rating::query()
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit($page->limit)
            ->offset($page->offset)
            ->with('recipient')
            ->get();

        return view('ratings/rathistory_gave', compact('ratings', 'user', 'page'));
    }

    /**
     *  Удаление истории
     */
    public function delete()
    {
        $id    = int(Request::input('id'));
        $token = check(Request::input('token'));

        $validator = new Validator();
        $validator
            ->true(Request::ajax(), 'Это не ajax запрос!')
            ->true(isAdmin(User::ADMIN), 'Удалять рейтинг могут только администраторы')
            ->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
            ->notEmpty($id, ['Не выбрана запись для удаление!']);

        if ($validator->isValid()) {

            Rating::query()->find($id)->delete();

            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => current($validator->getErrors())
            ]);
        }
    }
}
