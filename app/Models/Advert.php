<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

/**
 * Class Advert
 *
 * @property int    $id
 * @property string $site
 * @property string $name
 * @property string $color
 * @property int    $bold
 * @property int    $user_id
 * @property int    $created_at
 * @property int    $deleted_at
 */
class Advert extends Model
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
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'user_id' => 'int',
        ];
    }

    /**
     * Возвращает связь пользователя
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withDefault();
    }

    /**
     * Кеширует ссылки пользовательской рекламы
     */
    public static function statAdverts(): array
    {
        if (! setting('rekusershow')) {
            return [];
        }

        return Cache::remember('adverts', 1800, static function () {
            $data = self::query()->where('deleted_at', '>', SITETIME)->get();

            if ($data->isEmpty()) {
                return [];
            }

            $links = [];
            foreach ($data as $val) {
                $name = check($val->name);

                if ($val->color) {
                    $name = '<span style="color:' . $val->color . '">' . $name . '</span>';
                }

                $link = '<a href="' . $val->site . '" target="_blank" rel="nofollow">' . $name . '</a>';

                if ($val->bold) {
                    $link = '<b>' . $link . '</b>';
                }

                $links[] = $link;
            }

            return $links;
        });
    }
}
