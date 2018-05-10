<?php

namespace App\Models;

class Flood extends BaseModel
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
     * Определяет время антифлуда
     *
     * @return int
     */
    public static function getPeriod()
    {
        if (isAdmin()) {
            return 0;
        }

        $period = setting('floodstime');

        if (getUser('point') >= 100) {
            $period = round(setting('floodstime') / 2);
        }

        if (getUser('point') >= 300) {
            $period = round(setting('floodstime') / 3);
        }

        if (getUser('point') >= 500) {
            $period = round(setting('floodstime') / 6);
        }

        return $period;
    }

    /**
     * Проверяет сообщение на флуд
     *
     * @param int $period
     * @return bool
     */
    public static function isFlood($period = 0)
    {
        $userId = getUser('id');
        $period = $period ?: self::getPeriod();

        if (empty($period)) {
            return true;
        }

        self::query()->where('created_at', '<', SITETIME)->delete();

        $flood = self::query()
            ->where('user_id', $userId)
            ->where('page', server('PHP_SELF'))
            ->first();

        if (! $flood) {
            self::create([
                'user_id'    => $userId,
                'page'       => server('PHP_SELF'),
                'created_at' => SITETIME + $period,
            ]);

            return true;
        }

        return false;
    }
}
