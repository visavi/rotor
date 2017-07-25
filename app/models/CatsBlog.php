<?php

class CatsBlog extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'catsblog';

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
     * Возвращает количество статей за последние 3 дня
     *
     * @return mixed
     */
    public function new()
    {
        return $this->hasOne('Blog', 'category_id')
            ->select('category_id', Capsule::raw('count(*) as count'))
            ->where('created_at', '>', SITETIME - 86400 * 3)
            ->groupBy('category_id');
    }
}
