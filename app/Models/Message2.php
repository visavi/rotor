<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Inbox
 *
 * @property int id
 * @property int user_id
 * @property int author_id
 * @property string text
 * @property int created_at
 */
class Message2 extends BaseModel
{
    public const IN   = 'in';   // Принятые
    public const OUT  = 'out';  // Отправленные

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'messages2';

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
     * Morph name
     *
     * @var string
     */
    public static $morphName = 'messages';

    /**
     * Возвращает связь пользователей
     *
     * @return BelongsTo
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id')->withDefault();
    }
}
