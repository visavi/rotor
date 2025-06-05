<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\SearchableTrait;
use App\Traits\SortableTrait;
use App\Traits\UploadTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

/**
 * Class Item
 *
 * @property int    $id
 * @property int    $board_id
 * @property string $title
 * @property string $text
 * @property int    $user_id
 * @property int    $price
 * @property string $phone
 * @property int    $created_at
 * @property int    $updated_at
 * @property int    $expires_at
 * @property-read Board            $category
 * @property-read Collection<File> $files
 */
class Item extends BaseModel
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
    public string $uploadPath = '/uploads/boards';

    /**
     * Morph name
     */
    public static string $morphName = 'items';

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
            'date'  => ['field' => 'updated_at', 'label' => __('main.date')],
            'price' => ['field' => 'price', 'label' => __('main.cost')],
            'name'  => ['field' => 'title', 'label' => __('main.title')],
        ];
    }

    /**
     * Возвращает категорию объявлений
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Board::class, 'board_id')->withDefault();
    }

    /**
     * Возвращает загруженные файлы
     */
    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'relate');
    }

    /**
     * Возвращает путь к первому файлу
     *
     * @return HtmlString|null имя файла
     */
    public function getFirstImage(): ?HtmlString
    {
        $image = $this->files->first();

        $path = $image->path ?? null;

        return resizeImage($path, ['alt' => $this->title, 'class' => 'img-fluid']);
    }

    /**
     * Возвращает сокращенный текст объявления
     */
    public function shortText(int $words = 50): HtmlString
    {
        if (wordCount($this->text) > $words) {
            $this->text = bbCodeTruncate($this->text, $words);
        } else {
            $this->text = bbCode($this->text);
        }

        return new HtmlString($this->text);
    }

    /**
     * Удаление объявления и загруженных файлов
     */
    public function delete(): ?bool
    {
        return DB::transaction(function () {
            $this->files->each(static function (File $file) {
                $file->delete();
            });

            return parent::delete();
        });
    }
}
