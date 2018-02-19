<?php

namespace App\Models;

class Photo extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'photo';

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
     * Возвращает комментарии фотографий
     */
    public function comments()
    {
        return $this->morphMany(Comment::class, 'relate');
    }
}
