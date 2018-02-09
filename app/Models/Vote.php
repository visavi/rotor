<?php

namespace App\Models;

class Vote extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'vote';

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
     */
    public function topic()
    {
        return $this->belongsTo(Topic::class, 'topic_id')->withDefault();
    }

    /**
     * Возвращает варианты ответов
     */
    public function answers()
    {
        return $this->hasMany(VoteAnswer::class, 'vote_id')->orderBy('id');
    }

    /**
     * Возвращает связь с голосованиями
     */
    public function pollings()
    {
        return $this->morphOne(Polling::class, 'relate');
    }
}
