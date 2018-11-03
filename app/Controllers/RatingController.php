<?php

namespace App\Controllers;

use App\Classes\Validator;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Http\Request;

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
     *
     * @param string    $login
     * @param Request   $request
     * @param Validator $validator
     * @return string
     */
    public function index(string $login, Request $request, Validator $validator): string
    {
        $vote = $request->input('vote');
        $user = User::query()->where('login', $login)->first();

        if (! $user) {
            abort(404, 'Данного пользователя не существует!');
        }

        if (getUser('id') === $user->id) {
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

        if ($request->isMethod('post')) {

            $token = check($request->input('token'));
            $text  = check($request->input('text'));

            $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->length($text, 5, 250, ['text' => 'Слишком длинный или короткий комментарий!']);

            if (empty($vote) && getUser('rating') < 10) {
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
                    $text = 'Пользователь @' . getUser('login') . ' поставил вам плюс! (Ваш рейтинг: ' . ($user['rating'] + 1) . ')' . PHP_EOL . 'Комментарий: ' . $text;

                    $user->update([
                        'rating'    => DB::raw('posrating - negrating + 1'),
                        'posrating' => DB::raw('posrating + 1'),
                    ]);

                } else {

                    $text = 'Пользователь @' . getUser('login') . ' поставил вам минус! (Ваш рейтинг: ' . ($user['rating'] - 1) . ')' . PHP_EOL . 'Комментарий: ' . $text;

                    $user->update([
                        'rating'    => DB::raw('posrating - negrating - 1'),
                        'negrating' => DB::raw('negrating + 1'),
                    ]);
                }

                $user->sendMessage(null, $text);

                setFlash('success', 'Репутация успешно изменена!');
                redirect('/users/'.$user->login);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('ratings/index', compact('user', 'vote'));
    }

    /**
     *  Полученные голоса
     *
     * @param string $login
     * @return string
     */
    public function received(string $login): string
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
     *
     * @param string $login
     * @return string
     */
    public function gave(string $login): string
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
     *
     * @param Request   $request
     * @param Validator $validator
     * @return void
     * @throws \Exception
     */
    public function delete(Request $request, Validator $validator): void
    {
        $id    = int($request->input('id'));
        $token = check($request->input('token'));

        $validator
            ->true($request->ajax(), 'Это не ajax запрос!')
            ->true(isAdmin(User::ADMIN), 'Удалять рейтинг могут только администраторы')
            ->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
            ->notEmpty($id, ['Не выбрана запись для удаление!']);

        if ($validator->isValid()) {

            $rating = Rating::query()->find($id);

            if ($rating) {
                $rating->delete();
            }

            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => current($validator->getErrors())
            ]);
        }
    }
}
