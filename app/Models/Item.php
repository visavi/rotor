<?php

namespace App\Models;

class Item extends BaseModel
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
     * Возвращает категорию объявлений
     */
    public function category()
    {
        return $this->belongsTo(Board::class, 'board_id')->withDefault();
    }
}
