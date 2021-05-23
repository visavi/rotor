<?php

namespace App\Models;

use Faker\Provider\Base;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
 * @property int updated_at
 */
class Guestbook extends BaseModel
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'guestbook';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Возвращает связь пользователей
     *
     * @return BelongsTo
     */
    public function editUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edit_user_id')->withDefault();
    }

    /**
     * Morph name
     *
     * @var string
     */
    public static $morphName = 'guestbook';
}
