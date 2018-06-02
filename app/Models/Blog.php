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
     * Возвращает загруженные файлы
     */
    public function files()
    {
        return $this->morphMany(File::class, 'relate');
    }

    /**
     * Возвращает размер шрифта для облака тегов
     *
     * @param int $count
     * @param int $minCount
     * @param int $maxCount
     * @param int $minSize
     * @param int $maxSize
     * @return int
     */
    public static function logTagSize($count, $minCount, $maxCount, $minSize = 10, $maxSize = 30)
    {
        $minCount = log($minCount + 1);
        $maxCount = log($maxCount + 1);

        $diffSize  = $maxSize - $minSize;
        $diffCount = $maxCount - $minCount;

        if (empty($diffCount)) {
            $diffCount = 1;
        }

        return round($minSize + (log(1 + $count) - $minCount) * ($diffSize / $diffCount));
    }

    /**
     * Обрабатывает вставки изображений в тексте
     *
     * @return string текст статьт
     */
    public function parseAttach()
    {
        preg_match_all('/\[attach=(.*?)\]/', $this->text, $attachId);

        if (! empty($attachId[1])) {
            $attached = array_unique($attachId[1]);

            $images = File::query()
                ->where('relate_type', self::class)
                ->where('relate_id', $this->id)
                ->whereIn('id', $attached)
                ->pluck('hash', 'id')
                ->all();

            if ($images) {
                $search  = [];
                $replace = [];
                foreach ($images as $key => $image) {
                    $search[]  = '[attach=' . $key . ']';
                    $replace[] = '<img class="img-fluid" src="/uploads/blogs/' . $image . '" alt="image">';
                }

                return str_replace($search, $replace, $this->text);
            }
        }

        return $this->text;
    }
}
