<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\UploadTrait;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\HtmlString;

/**
 * Class Item
 *
 * @property int id
 * @property int board_id
 * @property string title
 * @property string text
 * @property int user_id
 * @property int price
 * @property string phone
 * @property int created_at
 * @property int updated_at
 * @property int expires_at
 * @property Board category
 * @property Collection files
 */
class Item extends BaseModel
{
    use UploadTrait;

    public const BOARD_PAGINATE = 10;

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
    public $uploadPath = '/uploads/boards';

    /**
     * Morph name
     *
     * @var string
     */
    public static $morphName = 'items';

    /**
     * Возвращает категорию объявлений
     *
     * @return BelongsTo
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

        $path = $image->hash ?? null;

        return resizeImage($path, ['alt' => $this->title, 'class' => 'img-fluid']);
    }

    /**
     * Возвращает сокращенный текст объявления
     *
     * @param int $words
     * @return HtmlString
     */
    public function shortText(int $words = 50): HtmlString
    {
        if (strlen($this->text) > $words) {
            $this->text = bbCodeTruncate($this->text, $words);
        }

        return new HtmlString($this->text);
    }

    /**
     * Удаление объявления и загруженных файлов
     *
     * @return bool|null
     * @throws Exception
     */
    public function delete(): ?bool
    {
        $this->files->each(static function (File $file) {
            $file->delete();
        });

        return parent::delete();
    }
}
