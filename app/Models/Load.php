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
        return $this->belongsTo(self::class, 'parent_id')->withDefault();
    }

    /**
     * Возвращает связь подкатегорий
     */
    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort');
    }

    /**
     * Возвращает количество загрузок за последние 3 дней
     *
     * @return mixed
     */
    public function new()
    {
        return $this->hasOne(Down::class, 'category_id')
            ->selectRaw('category_id, count(*) as count_downs')
            ->where('active', 1)
            ->where('created_at', '>', strtotime('-3 day', SITETIME))
            ->groupBy('category_id');
    }
}
