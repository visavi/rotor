<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Classes\Validator;
use App\Models\Rating;
use App\Models\User;
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
            abort(403, __('main.not_authorized'));
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
        $user = getUserByLogin($login);

        if (! $user) {
            abort(404, __('validator.user'));
        }

        if (getUser('id') === $user->id) {
            abort('default', __('ratings.reputation_yourself'));
        }

        if (getUser('point') < setting('editratingpoint')) {
            abort('default', __('ratings.reputation_point', ['point' => plural(setting('editratingpoint'), setting('scorename'))]));
        }

        // Голосовать за того же пользователя можно через 90 дней
        $getRating = Rating::query()
            ->where('user_id', getUser('id'))
            ->where('recipient_id', $user->id)
            ->where('created_at', '>', strtotime('-3 month', SITETIME))
            ->first();

        if ($getRating) {
            abort('default', __('ratings.reputation_already_changed'));
        }

        if ($request->isMethod('post')) {
            $token = check($request->input('token'));
            $text  = check($request->input('text'));

            $validator->equal($token, $_SESSION['token'], __('validator.token'))
                ->length($text, 5, 250, ['text' => __('validator.text')]);

            if ($vote === 'minus' && getUser('rating') < 1) {
                $validator->addError(__('ratings.reputation_positive'));
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
                    $user->increment('posrating');
                    $user->update(['rating' => $user->posrating - $user->negrating]);
                } else {
                    $user->increment('negrating');
                    $user->update(['rating' => $user->posrating - $user->negrating]);
                }

                $message = textNotice('rating', ['login' => getUser('login'), 'rating' => $user->rating, 'comment' => $text, 'vote' => __('main.' . $vote)]);
                $user->sendMessage(null, $message);

                setFlash('success', __('ratings.reputation_success_changed'));
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
        $user = getUserByLogin($login);

        if (! $user) {
            abort(404, __('validator.user'));
        }

        $ratings = Rating::query()
            ->where('recipient_id', $user->id)
            ->orderByDesc('created_at')
            ->with('user')
            ->paginate(setting('ratinglist'));

        return view('ratings/rathistory', compact('ratings', 'user'));
    }

    /**
     *  Отданные голоса
     *
     * @param string $login
     * @return string
     */
    public function gave(string $login): string
    {
        $user = getUserByLogin($login);

        if (! $user) {
            abort(404, __('validator.user'));
        }

        $ratings = Rating::query()
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->with('recipient')
            ->paginate(setting('ratinglist'));

        return view('ratings/rathistory_gave', compact('ratings', 'user'));
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
            ->true($request->ajax(), __('validator.not_ajax'))
            ->true(isAdmin(User::ADMIN), __('main.page_only_admins'))
            ->equal($token, $_SESSION['token'], __('validator.token'))
            ->notEmpty($id, [__('validator.deletion')]);

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
