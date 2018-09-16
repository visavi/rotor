<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Rating
 *
 * @property int id
 */
class Rating extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rating';

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
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id')->withDefault();
    }
}
