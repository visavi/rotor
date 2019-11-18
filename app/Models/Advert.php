<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Support\Facades\Cache;

/**
 * Class Advert
 *
 * @property int id
 * @property string site
 * @property string name
 * @property string color
 * @property int bold
 * @property int user_id
 * @property int created_at
 * @property int deleted_at
 */
class Advert extends BaseModel
{
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
     * Кэширует ссылки пользовательской рекламы
     *
     * @return array Список ссылок
     */
    public static function statAdverts(): array
    {
        if (setting('rekusershow')) {
            return Cache::remember('adverts', 1800, static function () {

                $data = self::query()->where('deleted_at', '>', SITETIME)->get();

                $links = [];
                if ($data->isNotEmpty()) {
                    foreach ($data as $val) {
                        if ($val->color) {
                            $val->name = '<span style="color:' . $val->color . '">' . $val->name . '</span>';
                        }

                        $link = '<a href="' . $val->site . '" target="_blank" rel="nofollow">' . $val->name . '</a>';

                        if ($val->bold) {
                            $link = '<b>' . $link . '</b>';
                        }

                        $links[] = $link;
                    }
                }

                return $links;
            });
        }

        return [];
    }
}
