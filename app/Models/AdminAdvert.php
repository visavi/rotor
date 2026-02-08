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
class AdminAdvert extends Model
{
    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
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
     * Кеширует ссылки админской рекламы
     */
    public static function statAdverts(): array
    {
        return Cache::remember('adminAdverts', 1800, static function () {
            $data = self::query()->where('deleted_at', '>', SITETIME)->get();

            $links = [];
            if ($data->isNotEmpty()) {
                foreach ($data as $val) {
                    $name = check($val->name);

                    if ($val->color) {
                        $name = '<span style="color:' . $val->color . '">' . $name . '</span>';
                    }

                    $link = '<a href="' . $val->site . '" target="_blank">' . $name . '</a>';

                    if ($val->bold) {
                        $link = '<b>' . $link . '</b>';
                    }

                    $links[] = $link;
                }
            }

            return $links;
        });
    }
}
