<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Guestbook
 *
 * @property int id
 * @property int user_id
 * @property string text
 * @property string ip
 * @property string brow
 * @property int created_at
 * @property string reply
 * @property int edit_user_id
 * @property bool active
 * @property int updated_at
 */
class Guestbook extends BaseModel
{
    /**
     * The table associated with the model.
     */
    protected $table = 'guestbook';

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     */
    protected $guarded = [];

    /**
     * Morph name
     */
    public static string $morphName = 'guestbook';

    /**
     * Возвращает связь пользователей
     */
    public function editUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edit_user_id')->withDefault();
    }
}
