<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Class Vote
 *
 * @property int id
 * @property string title
 * @property int count
 * @property int closed
 * @property int created_at
 * @property int topic_id
 * @property Topic topic
 * @property Collection answers
 */
class Vote extends BaseModel
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
     * Morph name
     *
     * @var string
     */
    public static $morphName = 'votes';

    /**
     * Возвращает топик
     *
     * @return BelongsTo
     */
    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class, 'topic_id')->withDefault();
    }

    /**
     * Возвращает варианты ответов
     *
     * @return HasMany
     */
    public function answers(): HasMany
    {
        return $this->hasMany(VoteAnswer::class, 'vote_id')->orderBy('id');
    }

    /**
     * Возвращает связь с голосованиями
     */
    public function pollings(): MorphMany
    {
        return $this->morphMany(Polling::class, 'relate');
    }
}
