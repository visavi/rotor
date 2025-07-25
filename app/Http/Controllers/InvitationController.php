<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Invitation\StoreRequest;
use App\Models\Invite;
use App\Models\User;
use App\Services\InviteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InvitationController extends Controller
{
    public ?User $user;

    /**
     * Конструктор
     */
    public function __construct(private InviteService $inviteService)
    {
        $this->middleware('check.user');
        $this->user = getUser();

        if ($this->user && $this->user->rating < setting('invite_rating')) {
            abort(403, __('invitations.access'));
        }
    }

    /**
     * Главная страница
     */
    public function index(Request $request): View
    {
        $used = $request->input('used') ? 1 : 0;

        $invites = Invite::query()
            ->where('user_id', $this->user->id)
            ->where('used', $used)
            ->orderByDesc('created_at')
            ->with('user', 'inviteUser')
            ->paginate(setting('listinvite'))
            ->appends(['used' => $used]);

        $lastInvite = $this->inviteService->getLastInviteByUserId($this->user->id);

        return view('invitations/index', compact('invites', 'used', 'lastInvite'));
    }

    /**
     * Создание ключей
     */
    public function store(StoreRequest $request): RedirectResponse
    {
        $newKeys = [];

        for ($i = 0; $i < setting('invite_count'); $i++) {
            $newKeys[] = [
                'hash'       => Str::random(),
                'user_id'    => $this->user->id,
                'created_at' => SITETIME,
            ];
        }

        Invite::query()->insert($newKeys);

        return redirect('/invitations')->with('success', __('admin.invitations.keys_success_created'));
    }
}
