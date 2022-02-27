<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\UploadTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\HtmlString;

/**
 * Class Article
 *
 * @property int id
 * @property int category_id
 * @property int user_id
 * @property string title
 * @property string text
 * @property string tags
 * @property int rating
 * @property int visits
 * @property int count_comments
 * @property int created_at
 * @property Collection files
 * @property Blog category
 */
class Article extends BaseModel
{
    use UploadTrait;

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
     * Директория загрузки файлов
     *
     * @var string
     */
    public $uploadPath = '/uploads/articles';

    /**
     * Counting field
     *
     * @var string
     */
    public $countingField = 'visits';

    /**
     * Morph name
     *
     * @var string
     */
    public static $morphName = 'articles';

    /**
     * Возвращает комментарии блогов
     *
     * @return MorphMany
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'relate')->with('relate');
    }

    /**
     * Возвращает последнии комментарии к статье
     *
     * @param int $limit
     * @return HasMany
     */
    public function lastComments(int $limit = 15): HasMany
    {
        return $this->hasMany(Comment::class, 'relate_id')
            ->where('relate_type', self::$morphName)
            ->orderBy('created_at', 'desc')
            ->with('user')
            ->limit($limit);
    }

    /**
     * Возвращает связь категории блога
     *
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Blog::class, 'category_id')->withDefault();
    }

    /**
     * Возвращает загруженные файлы
     *
     * @return MorphMany
     */
    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'relate');
    }

    /**
     * Возвращает связь с голосованием
     *
     * @return morphOne
     */
    public function polling(): morphOne
    {
        return $this->morphOne(Polling::class, 'relate')->where('user_id', getUser('id'));
    }

    /**
     * Возвращает путь к первому файлу
     *
     * @return HtmlString|null код изображения
     */
    public function getFirstImage(): ?HtmlString
    {
        $image = $this->files->first();

        if (! $image) {
            return null;
        }

        return new HtmlString('<img src="' . $image->hash . '" atl="' . $this->title . '" class="card-img-top">');
    }

    /**
     * Возвращает сокращенный текст статьи
     *
     * @param int $words
     *
     * @return HtmlString
     */
    public function shortText(int $words = 100): HtmlString
    {
        $more = view('app/_more', ['link' => '/articles/' . $this->id]);

        if (strpos($this->text, '[cut]') !== false) {
            $this->text = bbCode(current(explode('[cut]', $this->text)));
        } else {
            $this->text = bbCodeTruncate($this->text, $words);
        }

        return new HtmlString($this->text . $more);
    }

    /**
     * Возвращает размер шрифта для облака тегов
     *
     * @param int   $count
     * @param float $minCount
     * @param float $maxCount
     * @param int   $minSize
     * @param int   $maxSize
     *
     * @return int
     */
    public static function logTagSize($count, $minCount, $maxCount, $minSize = 10, $maxSize = 30): int
    {
        $minCount = log($minCount + 1);
        $maxCount = log($maxCount + 1);

        $diffSize  = $maxSize - $minSize;
        $diffCount = $maxCount - $minCount;

        if (empty($diffCount)) {
            $diffCount = 1;
        }

        return (int) round($minSize + (log(1 + $count) - $minCount) * ($diffSize / $diffCount));
    }

    /**
     * Удаление статьи и загруженных файлов
     *
     * @return bool|null
     */
    public function delete(): ?bool
    {
        $this->files->each(static function (File $file) {
            $file->delete();
        });

        return parent::delete();
    }
}
