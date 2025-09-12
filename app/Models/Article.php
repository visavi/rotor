<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\SearchableTrait;
use App\Traits\SortableTrait;
use App\Traits\UploadTrait;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/**
 * Class Article
 *
 * @property int    $id
 * @property int    $category_id
 * @property int    $user_id
 * @property string $title
 * @property string $slug
 * @property string $text
 * @property int    $rating
 * @property int    $visits
 * @property int    $count_comments
 * @property int    $created_at
 * @property bool   $active
 * @property bool   $draft
 * @property Date   $published_at
 * @property-read Collection<File>    $files
 * @property-read Collection<Comment> $comments
 * @property-read Collection<Poll>    $polls
 * @property-read Poll                $poll
 * @property-read Blog                $category
 */
class Article extends BaseModel
{
    use SearchableTrait;
    use SortableTrait;
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
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'active'       => 'bool',
            'published_at' => 'datetime',
        ];
    }

    /**
     * Возвращает поля участвующие в поиске
     */
    public function searchableFields(): array
    {
        return ['title', 'text'];
    }

    /**
     * Возвращает список сортируемых полей
     */
    protected static function sortableFields(): array
    {
        return [
            'date'     => ['field' => 'created_at', 'label' => __('main.date')],
            'name'     => ['field' => 'title', 'label' => __('main.title')],
            'visits'   => ['field' => 'visits', 'label' => __('main.views')],
            'rating'   => ['field' => 'rating', 'label' => __('main.rating')],
            'comments' => ['field' => 'count_comments', 'label' => __('main.comments')],
        ];
    }

    /**
     * Get the slug
     */
    protected function slug(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => str_replace(['%id%', '%slug%'], [$this->id, $value], setting('slug_template')),
            set: fn ($value) => Str::slug($this->title),
        );
    }

    /**
     * Scope a query to only include active records.
     */
    #[Scope]
    protected function active(Builder $query, bool $active = true): void
    {
        $query->where('active', $active);
    }

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
     * Возвращает связь с голосованиями
     */
    public function polls(): MorphMany
    {
        return $this->MorphMany(Poll::class, 'relate');
    }

    /**
     * Возвращает связь с голосованием
     */
    public function poll(): morphOne
    {
        return $this->morphOne(Poll::class, 'relate')
            ->where('user_id', getUser('id'));
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
     */
    public function getFirstImage(): ?HtmlString
    {
        $image = $this->files->first();

        if (! $image) {
            return null;
        }

        return new HtmlString('<img src="' . $image->path . '" atl="' . $this->title . '" class="card-img-top">');
    }

    /**
     * Возвращает сокращенный текст статьи
     */
    public function shortText(int $words = 100): HtmlString
    {
        $more = view('app/_more', ['link' => route('articles.view', ['slug' => $this->slug])]);

        if (str_contains($this->text, '[cut]')) {
            $result = bbCode(current(explode('[cut]', $this->text))) . $more;
        } elseif (wordCount($this->text) > $words) {
            $result = bbCodeTruncate($this->text, $words) . $more;
        } else {
            $result = bbCode($this->text)->toHtml();
        }

        $result = str_replace(
            'data-fancybox="gallery"',
            'data-fancybox="' . $this->getMorphClass() . '-' . $this->id . '"',
            $result
        );

        return new HtmlString($result);
    }

    /**
     * Is new article
     */
    public function isNew(): bool
    {
        return $this->created_at > strtotime('-3 day');
    }

    /*
     * Is published
     */
    public function isPublished(): bool
    {
        return $this->published_at === null || ! $this->published_at->isFuture();
    }

    /**
     * Удаление статьи и загруженных файлов
     */
    public function delete(): ?bool
    {
        return DB::transaction(function () {
            $this->polls()->delete();

            $this->comments->each(static function (Comment $comment) {
                $comment->delete();
            });

            $this->files->each(static function (File $file) {
                $file->delete();
            });

            return parent::delete();
        });
    }
}
