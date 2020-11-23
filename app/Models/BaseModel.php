<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Class BaseModel
 *
 * @property User user
 * @method increment(string $field, $amount = 1, array $extra = [])
 * @method decrement(string $field, $amount = 1, array $extra = [])
 * @package App\Models
 */
class BaseModel extends Model
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'user_id' => 'int',
    ];

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        Relation::morphMap([
            Down::$morphName      => Down::class,
            Article::$morphName   => Article::class,
            Photo::$morphName     => Photo::class,
            Offer::$morphName     => Offer::class,
            News::$morphName      => News::class,
            Topic::$morphName     => Topic::class,
            Post::$morphName      => Post::class,
            Guestbook::$morphName => Guestbook::class,
            Message::$morphName   => Message::class,
            Wall::$morphName      => Wall::class,
            Comment::$morphName   => Comment::class,
            Vote::$morphName      => Vote::class,
            Item::$morphName      => Item::class,
        ]);
    }

    /**
     * Возвращает связь пользователей
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withDefault();
    }

    /**
     * Возвращает логин пользователя
     *
     * @param string|null $value
     *
     * @return string
     */
    public function getLoginAttribute($value): string
    {
        return $value ?? setting('deleted_user');
    }
}
