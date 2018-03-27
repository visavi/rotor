<?php

namespace App\Models;

class Blog extends BaseModel
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
     * Возвращает комментарии блогов
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function comments()
    {
        return $this->morphMany(Comment::class, 'relate');
    }

    /**
     * Возвращает последнии комментарии к статье
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function lastComments($limit = 15)
    {
        return $this->hasMany(Comment::class, 'relate_id')
            ->where('relate_type', self::class)
            ->limit($limit);
    }

    /**
     * Возвращает связь категории блога
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id')->withDefault();
    }

    /**
     * Возвращает размер шрифта для облака тегов
     *
     * @param int $count
     * @param int $minCount
     * @param int $maxCount
     * @param int $minSize
     * @param int $maxSize
     * @return float|int
     */
    public static function logTagSize($count, $minCount, $maxCount, $minSize = 10, $maxSize = 20)
    {
        $minCount = log($minCount + 1);
        $maxCount = log($maxCount + 1);

        if ($count == 0) {
            return 0;
        }

        $diffSize  = $maxSize - $minSize;
        $diffCount = $maxCount - $minCount;

        return round($minSize + (log(1 + $count) - $minCount) * ($diffSize / $diffCount));
    }
}
