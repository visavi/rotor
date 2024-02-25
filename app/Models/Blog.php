<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\CategoryTreeTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class Category
 *
 * @property int id
 * @property int sort
 * @property int parent_id
 * @property string name
 * @property int count_articles
 * @property int closed
 * @property Collection children
 */
class Blog extends BaseModel
{
    use CategoryTreeTrait;

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     */
    protected $guarded = [];

    /**
     * Возвращает связь родительской категории
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id')->withDefault();
    }

    /**
     * Возвращает связь подкатегорий
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort');
    }

    /**
     * Возвращает количество статей за последние 3 дня
     */
    public function new(): hasOne
    {
        return $this->hasOne(Article::class, 'category_id')
            ->selectRaw('category_id, count(*) as count_articles')
            ->where('created_at', '>', strtotime('-3 day', SITETIME))
            ->groupBy('category_id');
    }
}
