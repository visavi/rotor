<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\SearchableTrait;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Class Comment
 *
 * @property int id
 * @property int user_id
 * @property string relate_type
 * @property int relate_id
 * @property string text
 * @property string ip
 * @property string brow
 * @property int created_at
 */
class Comment extends BaseModel
{
    use SearchableTrait;

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     */
    protected $guarded = [];

    /**
     * Morph name
     */
    public static string $morphName = 'comment';

    /**
     * Возвращает поля участвующие в поиске
     */
    public function searchableFields(): array
    {
        return ['text'];
    }

    /**
     * Возвращает связанные объекты
     */
    public function relate(): MorphTo
    {
        return $this->morphTo('relate');
    }

    /**
     * Возвращает связь с голосованием
     */
    public function polling(): morphOne
    {
        return $this->morphOne(Polling::class, 'relate')->where('user_id', getUser('id'));
    }

    /**
     * Возвращает тип связанного объекта
     */
    public function getRelateType(): string
    {
        return match ($this->relate_type) {
            Article::$morphName => __('index.blogs'),
            Down::$morphName    => __('index.loads'),
            News::$morphName    => __('index.news'),
            Offer::$morphName   => __('index.offers'),
            Photo::$morphName   => __('index.photos'),
        };
    }
}
