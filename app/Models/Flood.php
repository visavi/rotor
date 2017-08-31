<?php

namespace App\Models;

class Flood extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'flood';


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
        $period = setting('floodstime');

        if (user('point') < 50) {
            $period = round(setting('floodstime') * 2);
        }
        if (user('point') >= 500) {
            $period = round(setting('floodstime') / 2);
        }
        if (user('point') >= 1000) {
            $period = round(setting('floodstime') / 3);
        }
        if (user('point') >= 5000) {
            $period = round(setting('floodstime') / 6);
        }
        if (isAdmin()) {
            $period = 0;
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
        $userId = getUserId();
        $period = $period ?: self::getPeriod();

        if (empty($period)) {
            return true;
        }

        Flood::where('created_at', '<', SITETIME)->delete();

        $flood = Flood::where('user_id', $userId)
            ->where('page', server('PHP_SELF'))
            ->first();

        if (! $flood) {
            Flood::create([
                'user_id'    => $userId,
                'page'       => server('PHP_SELF'),
                'created_at' => SITETIME + $period,
            ]);

            return true;
        }

        return false;
    }
}
