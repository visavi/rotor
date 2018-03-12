<?php

namespace App\Models;

class Load extends BaseModel
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
        return $this->belongsTo(Load::class, 'parent_id')->withDefault();
    }

    /**
     * Возвращает связь подкатегорий
     */
    public function children()
    {
        return $this->hasMany(Load::class, 'parent_id')->orderBy('sort');
    }

    /**
     * Возвращает количество загрузок за последние 5 дней
     *
     * @return mixed
     */
    public function new()
    {
        return $this->hasOne(Down::class, 'category_id')
            ->selectRaw('category_id, count(*) as count_downs')
            ->where('created_at', '>', SITETIME - 86400 * 3)
            ->groupBy('category_id');
    }
}
