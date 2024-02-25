<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Invite
 *
 * @property int id
 * @property string hash
 * @property int user_id
 * @property int invite_user_id
 * @property int used
 * @property int used_at
 * @property int created_at
 */
class Invite extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'invite';

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     */
    protected $guarded = [];

    /**
     * Возвращает связь пользователей
     */
    public function inviteUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invite_user_id')->withDefault();
    }
}
