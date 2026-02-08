<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

/**
 * Class PaidAdvert
 *
 * @property int    $id
 * @property string $place
 * @property string $site
 * @property array  $names
 * @property string $color
 * @property bool   $bold
 * @property int    $user_id
 * @property int    $created_at
 * @property int    $deleted_at
 */
class PaidAdvert extends Model
{
    public const TOP_ALL = 'top_all';
    public const TOP = 'top';
    public const FORUM = 'forum';
    public const BOTTOM_ALL = 'bottom_all';
    public const BOTTOM = 'bottom';

    /**
     * Места размещения
     */
    public const PLACES = [
        self::TOP_ALL,
        self::TOP,
        self::FORUM,
        self::BOTTOM_ALL,
        self::BOTTOM,
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'bold'    => 'bool',
            'names'   => 'array',
            'user_id' => 'int',
        ];
    }

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     */
    protected $guarded = [];

    /**
     * Возвращает связь пользователя
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withDefault();
    }

    /**
     * Get places
     */
    public function getPlaces(): array
    {
        $places = [];
        foreach (self::PLACES as $place) {
            $places[$place] = __('admin.paid_adverts.' . $place);
        }

        return $places;
    }

    /**
     * Возвращает название места размещения
     */
    public function getPlaceName(): string
    {
        return $this->getPlaces()[$this->place] ?? 'Unknown';
    }

    /**
     * Кеширует ссылки платной рекламы
     */
    public static function statAdverts(): array
    {
        return Cache::remember('paidAdverts', 3600, static function () {
            $data = self::query()->where('deleted_at', '>', SITETIME)->orderBy('created_at')->get();

            $links = [];
            if ($data->isNotEmpty()) {
                foreach ($data as $val) {
                    $names = check($val->names);

                    $sites = [];
                    foreach ($names as $name) {
                        if ($val->color) {
                            $name = '<span style="color:' . $val->color . '">' . $name . '</span>';
                        }

                        $link = '<a href="' . $val->site . '" target="_blank">' . $name . '</a>';

                        if ($val->bold) {
                            $link = '<b>' . $link . '</b>';
                        }

                        $sites[] = $link;
                    }

                    $links[$val->place][] = $sites;
                }
            }

            return $links;
        });
    }
}
