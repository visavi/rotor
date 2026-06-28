<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\HtmlCast;
use App\Classes\Registry;
use App\Traits\FileableTrait;
use App\Traits\PollableTrait;
use App\Traits\SearchableTrait;
use App\Traits\UploadTrait;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/**
 * Class Comment
 *
 * @property int                  $id
 * @property int                  $user_id
 * @property string               $relate_type
 * @property int                  $relate_id
 * @property string               $text
 * @property string               $ip
 * @property string               $brow
 * @property ?int                 $parent_id
 * @property int                  $depth
 * @property CarbonImmutable      $created_at
 * @property CarbonImmutable|null $deleted_at
 * @property-read User                      $user
 * @property-read ?Model                    $relate
 * @property-read Collection<int, File>     $files
 * @property-read Collection<int, self>     $children
 * @property-read Collection<int, Poll>     $polls
 * @property-read Poll                      $poll
 */
class Comment extends Model
{
    use PollableTrait;
    use FileableTrait;
    use SearchableTrait;
    use UploadTrait;

    /**
     * The name of the "updated at" column.
     */
    public const ?string UPDATED_AT = null;

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
            'deleted_at' => 'datetime',
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
     * Видимые комментарии — у которых relate_type зарегистрирован в morphMap
     * (комментарии отключённых модулей скрываются)
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->whereIn('relate_type', array_keys(Relation::morphMap()));
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

            $this->update(['text' => null, 'deleted_at' => now()]);
        });
    }

    /**
     * Возвращает тип связанного объекта
     */
    public function getViewUrl(bool $absolute = true): string
    {
        $plural = Str::plural($this->relate_type);
        $params = $this->relate_type === 'articles'
            ? ['slug' => $this->relate?->getAttribute('slug')]
            : ['id' => $this->relate_id];

        return route($plural . '.view', $params, $absolute) . '#comment_' . $this->id;
    }

    /**
     * Возвращает тип связанного объекта
     */
    public function getRelateType(): string
    {
        return Registry::$labelTypes[$this->relate_type] ?? $this->relate_type;
    }
}
