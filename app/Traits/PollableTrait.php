<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Poll;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Query\JoinClause;

trait PollableTrait
{
    /**
     * Возвращает связь с голосованиями
     *
     * @return MorphMany<Poll, $this>
     */
    public function polls(): MorphMany
    {
        return $this->morphMany(Poll::class, 'relate');
    }

    /**
     * Возвращает связь с голосованием текущего пользователя
     *
     * @return MorphOne<Poll, $this>
     */
    public function poll(): MorphOne
    {
        return $this->morphOne(Poll::class, 'relate')
            ->where('user_id', getUser('id'));
    }

    /**
     * Подмешивает голос текущего пользователя в поле vote (гостю null) —
     * для списков, где связь poll на каждую запись была бы лишним запросом
     */
    public function scopeWithUserVote(Builder $query): Builder
    {
        $model = $query->getModel();

        if ($query->getQuery()->columns === null) {
            $query->select($model->qualifyColumn('*'));
        }

        return $query->addSelect('polls.vote')
            ->leftJoin('polls', static function (JoinClause $join) use ($model) {
                $join->on($model->getQualifiedKeyName(), 'polls.relate_id')
                    ->where('polls.relate_type', $model->getMorphClass())
                    ->where('polls.user_id', getUser('id'));
            });
    }
}
