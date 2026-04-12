<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Classes\Validator;
use App\Models\File;
use App\Models\Flood;
use App\Models\Guestbook;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GuestbookController extends Controller
{
    /**
     * Главная страница
     */
    public function index(): View
    {
        $posts = Guestbook::query()
            ->active()
            ->orderByDesc('created_at')
            ->with('user', 'editUser', 'files')
            ->paginate(10);

        $unpublished = Guestbook::query()->active(false)->count();

        $files = collect();
        if ($user = getUser()) {
            $files = File::query()
                ->where('relate_type', Guestbook::$morphName)
                ->where('relate_id', 0)
                ->where('user_id', $user->id)
                ->orderBy('created_at')
                ->get();
        }

        return view('guestbook/index', compact('posts', 'unpublished', 'files'));
    }

    /**
     * Добавление сообщения
     */
    public function add(Request $request, Validator $validator, Flood $flood): RedirectResponse
    {
        $msg = $request->input('msg');
        $user = $request->user();

        $validator->length($msg, setting('guestbook_text_min'), setting('guestbook_text_max'), ['msg' => __('validator.text')])
            ->false($flood->isFlood(), ['msg' => __('validator.flood', ['sec' => $flood->getPeriod()])]);

        /* Проверка для гостей */
        if (! $user && setting('bookadds')) {
            $validator->true(captchaVerify(), ['protect' => __('validator.captcha')]);
            $validator->true(! str_contains($msg ?? '', '//'), ['msg' => __('guestbook.without_links')]);
            $validator->length($request->input('guest_name'), 3, 20, ['guest_name' => __('users.name_short_or_long')], false);
        } else {
            $validator->true($user, ['msg' => __('main.not_authorized')]);
        }

        if ($validator->isValid()) {
            $msg = antimat($msg);
            $active = ! setting('guest_moderation');
            $guestName = $request->input('guest_name');

            if ($user) {
                $active = true;
                $guestName = null;

                $user->increment('allguest');
                $user->increment('point', setting('guestbook_point'));
                $user->increment('money', setting('guestbook_money'));
            } else {
                // Гость пишет без tiptap — оборачиваем plain text в HTML
                $msg = '<p>' . nl2br(check($msg), false) . '</p>';
            }

            $guestbook = Guestbook::query()->create([
                'user_id'    => $user->id ?? null,
                'text'       => $msg,
                'ip'         => getIp(),
                'brow'       => getBrowser(),
                'guest_name' => $guestName,
                'active'     => $active,
                'created_at' => SITETIME,
            ]);

            if ($user) {
                File::query()
                    ->where('relate_type', Guestbook::$morphName)
                    ->where('relate_id', 0)
                    ->where('user_id', $user->id)
                    ->update(['relate_id' => $guestbook->id]);
            }

            clearCache('statGuestbook');
            $flood->saveState();

            sendNotify(strip_tags($msg), route('guestbook.index', absolute: false), __('index.guestbook'));

            return redirect()
                ->route('guestbook.index')
                ->with('success', $active ? __('main.message_added_success') : __('main.message_publish_moderation'));
        }

        return redirect()->route('guestbook.index')
            ->withErrors($validator->getErrors())
            ->withInput();
    }

    /**
     * Редактирование сообщения
     */
    public function edit(int $id, Request $request, Validator $validator): View|RedirectResponse
    {
        if (! $user = getUser()) {
            abort(403);
        }

        $msg = $request->input('msg');

        $post = Guestbook::query()->where('user_id', $user->id)->find($id);

        if (! $post) {
            abort(404, __('main.message_not_found'));
        }

        if ($post->created_at + 600 < SITETIME) {
            abort(200, __('main.editing_impossible'));
        }

        if ($request->isMethod('post')) {
            $validator->length($msg, setting('guestbook_text_min'), setting('guestbook_text_max'), ['msg' => __('validator.text')]);

            if ($validator->isValid()) {
                $post->update([
                    'text'         => antimat($msg),
                    'edit_user_id' => $user->id,
                    'updated_at'   => SITETIME,
                ]);

                return redirect()
                    ->route('guestbook.index')
                    ->with('success', __('main.message_edited_success'));
            }

            return back()->withErrors($validator->getErrors())->withInput();
        }

        return view('guestbook/edit', compact('post'));
    }
}
