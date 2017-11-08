<?php

namespace App\Models;

class Down extends BaseModel
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
     * Возвращает категорию загрузок
     */
    public function category()
    {
        return $this->belongsTo(Cats::class, 'category_id')->withDefault();
    }

    /**
     * Возвращает логин пользователя
     *
     * @param string $value
     * @return string
     */
    public function getFolderAttribute()
    {
        return $this->category->folder ? $this->category->folder.'/' : '';
    }
}
