<?php

namespace App\Models;

class Ignore extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ignoring';

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
    public function ignoring()
    {
        return $this->belongsTo(User::class, 'ignore_id');
    }

    /**
     * Возвращает объект пользователя
     */
    public function getIgnore()
    {
        return $this->ignoring ? $this->ignoring : new User();
    }
}
