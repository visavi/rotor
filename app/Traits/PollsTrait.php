<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Poll;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait PollsTrait
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
}
