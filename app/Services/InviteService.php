<?php

namespace App\Services;

use App\Models\Invite;
use Illuminate\Database\Eloquent\Model;

class InviteService
{
    /**
     * Get last invite by userId
     */
    public function getLastInviteByUserId(int $userId): ?Model
    {
        return Invite::query()
            ->where('user_id', $userId)
            ->where('created_at', '>', strtotime('-' . setting('invite_days') . ' days', SITETIME))
            ->first();
    }
}
