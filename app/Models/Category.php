<?php

namespace App\Models;

use Illuminate\Database\Capsule\Manager as DB;

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
     * Возвращает количество статей за последние 3 дня
     *
     * @return mixed
     */
    public function new()
    {
        return $this->hasOne(Blog::class, 'category_id')
            ->select('category_id', DB::raw('count(*) as count'))
            ->where('created_at', '>', SITETIME - 86400 * 3)
            ->groupBy('category_id');
    }
}
