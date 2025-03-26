<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\UploadTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     */
    protected $guarded = [];

    /**
     * Директория загрузки файлов
     */
    public string $uploadPath = '/uploads/articles';

    /**
     * Counting field
     */
    public string $countingField = 'visits';

    /**
     * Morph name
     */
    public static string $morphName = 'articles';

    /**
     * Возвращает комментарии блогов
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'relate')->with('relate');
    }

    /**
     * Возвращает последние комментарии к статье
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
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Blog::class, 'category_id')->withDefault();
    }

    /**
     * Возвращает загруженные файлы
     */
    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'relate');
    }

    /**
     * Возвращает связь с голосованием
     */
    public function polling(): morphOne
    {
        return $this->morphOne(Polling::class, 'relate')->where('user_id', getUser('id'));
    }

    /**
     * Tags
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'article_tags', 'article_id', 'tag_id')
            ->withPivot('sort')
            ->orderBy('article_tags.sort');
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
     */
    public function shortText(int $words = 100): HtmlString
    {
        $more = view('app/_more', ['link' => '/articles/' . $this->id]);

        if (str_contains($this->text, '[cut]')) {
            $this->text = bbCode(current(explode('[cut]', $this->text)));

            return new HtmlString($this->text . $more);
        }

        if (wordCount($this->text) > $words) {
            $this->text = bbCodeTruncate($this->text, $words);

            return new HtmlString($this->text . $more);
        }

        return new HtmlString(bbCode($this->text));
    }

    /**
     * Удаление статьи и загруженных файлов
     */
    public function delete(): ?bool
    {
        $this->files->each(static function (File $file) {
            $file->delete();
        });

        return parent::delete();
    }
}
