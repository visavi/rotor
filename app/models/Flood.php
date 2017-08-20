<?php

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
        $period = Setting::get('floodstime');

        if (App::user('point') < 50) {
            $period = round(Setting::get('floodstime') * 2);
        }
        if (App::user('point') >= 500) {
            $period = round(Setting::get('floodstime') / 2);
        }
        if (App::user('point') >= 1000) {
            $period = round(Setting::get('floodstime') / 3);
        }
        if (App::user('point') >= 5000) {
            $period = round(Setting::get('floodstime') / 6);
        }
        if (is_admin()) {
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
        $userId = App::getUserId();
        $period = $period ?: self::getPeriod();

        if (empty($period)) {
            return true;
        }

        Flood::where('created_at', '<', SITETIME)->delete();

        $flood = Flood::where('user_id', $userId)
            ->where('page', App::server('PHP_SELF'))
            ->first();

        if (! $flood) {
            Flood::create([
                'user_id'    => $userId,
                'page'       => App::server('PHP_SELF'),
                'created_at' => SITETIME + $period,
            ]);

            return true;
        }

        return false;
    }
}
