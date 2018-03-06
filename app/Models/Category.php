<?php

namespace App\Models;

class Category extends BaseModel
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
     * Возвращает связь родительской категории
     */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Возвращает связь подкатегорий
     */
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort');
    }

    /**
     * Возвращает количество статей за последние 3 дня
     *
     * @return mixed
     */
    public function new()
    {
        return $this->hasOne(Blog::class, 'category_id')
            ->selectRaw('category_id, count(*) as count_blogs')
            ->where('created_at', '>', SITETIME - 86400 * 3)
            ->groupBy('category_id');
    }
}
