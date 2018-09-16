<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Bookmark
 *
 * @property int id
 */
class Bookmark extends BaseModel
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
     * Возвращает топик
     *
     * @return BelongsTo
     */
    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class, 'topic_id')->withDefault();
    }
}
