<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Classes\Validator;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RatingController extends Controller
{
    public ?User $user;

    /**
     * Конструктор
     */
    public function __construct()
    {
        $this->middleware('check.user');

        $this->middleware(function ($request, $next) {
            $this->user = getUser();

            return $next($request);
        });
    }

    /**
     * Изменение рейтинга
     *
     *
     * @return View|RedirectResponse
     */
    public function index(string $login, Request $request, Validator $validator)
    {
        $vote = $request->input('vote');
        $user = getUserByLogin($login);

        if (! $user) {
            abort(404, __('validator.user'));
        }

        if ($this->user->id === $user->id) {
            abort(200, __('ratings.reputation_yourself'));
        }

        if ($this->user->point < setting('editratingpoint')) {
            abort(200, __('ratings.reputation_point', ['point' => plural(setting('editratingpoint'), setting('scorename'))]));
        }

        // Голосовать за того же пользователя можно через 90 дней
        $getRating = Rating::query()
            ->where('user_id', $this->user->id)
            ->where('recipient_id', $user->id)
            ->where('created_at', '>', strtotime('-3 month', SITETIME))
            ->first();

        if ($getRating) {
            abort(200, __('ratings.reputation_already_changed'));
        }

        if ($request->isMethod('post')) {
            $text = $request->input('text');

            $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->length($text, 5, 250, ['text' => __('validator.text')]);

            if ($vote === 'minus' && $this->user->rating < 1) {
                $validator->addError(__('ratings.reputation_positive'));
            }

            if ($validator->isValid()) {
                $text = antimat($text);

                Rating::query()->create([
                    'user_id'      => $this->user->id,
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

                $message = textNotice('rating', ['login' => $this->user->login, 'rating' => $user->rating, 'comment' => $text, 'vote' => __('main.' . $vote)]);
                $user->sendMessage(null, $message);

                setFlash('success', __('ratings.reputation_success_changed'));

                return redirect('users/' . $user->login);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return view('ratings/index', compact('user', 'vote'));
    }

    /**
     *  Полученные голоса
     */
    public function received(string $login): View
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
     */
    public function gave(string $login): View
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
     */
    public function delete(Request $request, Validator $validator): JsonResponse
    {
        $id = int($request->input('id'));

        $validator
            ->true($request->ajax(), __('validator.not_ajax'))
            ->true(isAdmin(User::ADMIN), __('main.page_only_admins'))
            ->equal($request->input('_token'), csrf_token(), __('validator.token'))
            ->notEmpty($id, [__('validator.deletion')]);

        if ($validator->isValid()) {
            $rating = Rating::query()->find($id);

            if ($rating) {
                $rating->delete();
            }

            return response()->json(['success' => true]);
        }

        return response()->json([
            'success' => false,
            'message' => current($validator->getErrors()),
        ]);
    }
}
