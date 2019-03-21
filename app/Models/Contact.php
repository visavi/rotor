<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Contact
 *
 * @property int id
 * @property int user_id
 * @property int contact_id
 * @property string text
 * @property int created_at
 */
class Contact extends BaseModel
{
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
    public function contactor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'contact_id')->withDefault();
    }
}
