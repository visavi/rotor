<?php

namespace App\Models;

class Trash extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'trash';

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
    public function author()
    {
        return $this->belongsTo('User', 'author_id');
    }

    /**
     * Возвращает объект пользователя
     */
    public function getAuthor()
    {
        return $this->author ? $this->author : new User();
    }
}
