<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\HtmlCast;
use App\Traits\SearchableTrait;
use App\Traits\UploadTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/**
 * Class Comment
 *
 * @property int    $id
 * @property int    $user_id
 * @property string $relate_type
 * @property int    $relate_id
 * @property string $text
 * @property string $ip
 * @property string $brow
 * @property int    $created_at
 * @property int    $deleted_at
 * @property-read Collection<Poll> $polls
 * @property-read Poll             $poll
 */
class Comment extends Model
{
    use SearchableTrait;
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
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'user_id'    => 'int',
            'text'       => HtmlCast::class,
            'deleted_at' => 'int',
        ];
    }

    /**
    /**
     * Morph name
     */
    public static string $morphName = 'comments';

    /**
     * Директория загрузки файлов
     */
    public string $uploadPath = '/uploads/comments';

    /**
     * Возвращает поля участвующие в поиске
     */
    public function searchableFields(): array
    {
        return ['text'];
    }

    /**
     * Возвращает загруженные файлы
     */
    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'relate')
            ->orderBy('created_at');
    }

    /**
     * Возвращает файлы
     */
    public function getFiles(): Collection
    {
        return $this->files->filter(static fn (File $f) => ! $f->isImage() && ! $f->isVideo());
    }

    /**
     * Возвращает медиафайлы (картинки и видео)
     */
    public function getMedia(): Collection
    {
        return $this->files->filter(static fn (File $f) => $f->isImage() || $f->isVideo());
    }

    /**
     * Возвращает медиафайлы, не вставленные в текст
     */
    public function getDetachedMedia(): Collection
    {
        return $this->getMedia()->reject(fn (File $f) => str_contains($this->text ?? '', $f->path));
    }

    /**
     * Исключает мягко удалённые комментарии из всех запросов по умолчанию
     */
    protected static function booted(): void
    {
        static::addGlobalScope('active', static fn ($query) => $query->whereNull('deleted_at'));
    }

    /**
     * Возвращает дочерние комментарии (включая мягко удалённые для отображения заглушки)
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
            ->withoutGlobalScope('active')
            ->orderBy('created_at');
    }

    /**
     * Возвращает количество всех дочерних комментариев
     */
    public function countAllDescendants(): int
    {
        $count = $this->children->count();
        foreach ($this->children as $child) {
            $count += $child->countAllDescendants();
        }

        return $count;
    }

    /**
     * Возвращает родительский комментарий
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Возвращает связь пользователя
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withDefault();
    }

    /**
     * Возвращает связанные объекты
     */
    public function relate(): MorphTo
    {
        return $this->morphTo('relate');
    }

    /**
     * Возвращает связь с голосованиями
     */
    public function polls(): MorphMany
    {
        return $this->MorphMany(Poll::class, 'relate');
    }

    /**
     * Возвращает связь с голосованием пользователя
     */
    public function poll(): MorphOne
    {
        return $this->morphOne(Poll::class, 'relate')
            ->where('user_id', getUser('id'));
    }

    /**
     * Get text
     */
    public function getText(): HtmlString
    {
        return renderHtml($this->text, 'comment-' . $this->id);
    }

    /**
     * Удаление записи
     */
    public function delete(): ?bool
    {
        return DB::transaction(function () {
            $this->polls()->delete();

            $this->files->each(static function (File $file) {
                $file->delete();
            });

            return parent::delete();
        });
    }

    /**
     * Мягкое удаление: очищает контент, но сохраняет запись для дочерних комментариев
     */
    public function softDelete(): void
    {
        DB::transaction(function () {
            $this->polls()->delete();

            $this->files->each(static function (File $file) {
                $file->delete();
            });

            $this->update(['text' => null, 'deleted_at' => time()]);
        });
    }

    /**
     * Возвращает тип связанного объекта
     */
    public function getViewUrl(bool $absolute = true): string
    {
        $plural = Str::plural($this->relate_type);
        $params = $this->relate_type === 'articles'
            ? ['slug' => $this->relate->slug]
            : ['id' => $this->relate_id];

        return route($plural . '.view', $params, $absolute) . '#comment_' . $this->id;
    }

    /**
     * Возвращает тип связанного объекта
     */
    public function getRelateType(): string
    {
        return match ($this->relate_type) {
            'articles'        => __('index.blogs'),
            'downs'           => __('index.loads'),
            News::$morphName  => __('index.news'),
            'offers'          => __('index.offers'),
            'photos'          => __('index.photos'),
        };
    }
}
