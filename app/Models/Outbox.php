<?php

namespace App\Models;

class Outbox extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'outbox';

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
     */
    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id')->withDefault();
    }
}
