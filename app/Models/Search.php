<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Class Search
 *
 * @property int    $id
 * @property string $relate_type
 * @property int    $relate_id
 * @property string $text
 * @property int    $created_at
 */
class Search extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'search';

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     */
    protected $guarded = [];

    /**
     * Возвращает связанные объекты
     */
    public function relate(): MorphTo
    {
        return $this->morphTo('relate');
    }

    /**
     * Возвращает массив связанных объектов
     */
    public static function getRelateTypes(): array
    {
        return [
            Article::$morphName   => __('index.blogs'),
            Comment::$morphName   => __('index.comments'),
            Down::$morphName      => __('index.loads'),
            Guestbook::$morphName => __('index.guestbook'),
            Item::$morphName      => __('index.boards'),
            News::$morphName      => __('index.news'),
            Offer::$morphName     => __('index.offers'),
            Photo::$morphName     => __('index.photos'),
            Post::$morphName      => __('index.posts'),
            Topic::$morphName     => __('index.topics'),
            User::$morphName      => __('index.users'),
            Vote::$morphName      => __('index.votes'),
        ];
    }

    /**
     * Возвращает тип связанного объекта
     */
    public function getRelateType(): string
    {
        $relates = self::getRelateTypes();

        return $relates[$this->relate_type] ?? __('main.undefined');
    }
}
