<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Support\Facades\Cache;

/**
 * Class PaidAdvert
 *
 * @property int id
 * @property string place
 * @property string site
 * @property string name
 * @property string color
 * @property int bold
 * @property int user_id
 * @property int created_at
 * @property int deleted_at
 */
class PaidAdvert extends BaseModel
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
     * Кэширует ссылки платной рекламы
     *
     * @return array Список ссылок
     */
    public static function statAdverts(): array
    {
        return Cache::remember('paidAdverts', 3600, static function () {
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

                    $links[$val->place][] = $link;
                }
            }

            return $links;
        });
    }
}
