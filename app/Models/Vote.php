<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\HtmlCast;
use App\Traits\SearchableTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

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
 * @property-read Collection<Poll>       $polls
 */
class Vote extends Model
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
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'description' => HtmlCast::class,
        ];
    }

    /**
     * Возвращает поля участвующие в поиске
     */
    public function searchableFields(): array
    {
        return ['title', 'description'];
    }

    /**
     * Get description
     */
    public function getDescription(): HtmlString
    {
        return renderHtml($this->description, 'vote-' . $this->id);
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
        return $this->hasMany(VoteAnswer::class, 'vote_id')
            ->orderBy('id');
    }

    /**
     * Возвращает связь с голосованиями
     */
    public function polls(): MorphMany
    {
        return $this->morphMany(Poll::class, 'relate');
    }

    /**
     * Возвращает связь с голосованием
     */
    public function poll(): MorphOne
    {
        return $this->morphOne(Poll::class, 'relate')
            ->where('user_id', getUser('id'));
    }

    /**
     * Удаление голосования
     */
    public function delete(): ?bool
    {
        return DB::transaction(function () {
            $this->polls()->delete();

            $this->answers->each(function (VoteAnswer $answer) {
                $answer->delete();
            });

            return parent::delete();
        });
    }
}
