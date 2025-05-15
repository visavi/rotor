<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Sticker
 *
 * @property int    $id
 * @property int    $category_id
 * @property string $name
 * @property string $code
 */
class Sticker extends BaseModel
{
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
    public string $uploadPath = '/uploads/stickers';

    /**
     * Возвращает связь категории стикеров
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(StickersCategory::class, 'category_id')->withDefault();
    }
}
