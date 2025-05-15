<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\SearchableTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;

/**
 * Class Vote
 *
 * @property int    $id
 * @property string $title
 * @property string $description
 * @property int    $count
 * @property int    $closed
 * @property int    $created_at
 * @property int    $topic_id
 * @property-read Topic                  $topic
 * @property-read Collection<VoteAnswer> $answers
 * @property-read Collection<Polling>    $pollings
 */
class Vote extends BaseModel
{
    use SearchableTrait;

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
    public static string $morphName = 'votes';

    /**
     * Возвращает поля участвующие в поиске
     */
    public function searchableFields(): array
    {
        return ['title', 'description'];
    }

    /**
     * Возвращает топик
     */
    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class, 'topic_id')->withDefault();
    }

    /**
     * Возвращает варианты ответов
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

    /**
     * Удаление голосования
     */
    public function delete(): ?bool
    {
        return DB::transaction(function () {
            $this->answers->each(function (VoteAnswer $answer) {
                $answer->delete();
            });

            $this->pollings()->delete();

            return parent::delete();
        });
    }
}
