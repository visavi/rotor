<?php

class Setting extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'setting';

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
     * Возвращает настройки сайта по ключу
     *
     * @param  string $key ключ массива
     * @return string      данные
     */
    public static function get($key = null)
    {
        if (! Registry::has('setting')) {

            if (! file_exists(STORAGE.'/temp/setting.dat')) {
                $setting = self::pluck('value', 'name')->all();
                file_put_contents(STORAGE.'/temp/setting.dat', serialize($setting), LOCK_EX);
            }
            $setting = unserialize(file_get_contents(STORAGE.'/temp/setting.dat'));

            Registry::set('setting', $setting);
        }

        if (empty($key)) {
            return Registry::get('setting');
        }

        return isset(Registry::get('setting')[$key]) ? Registry::get('setting')[$key] : null;
    }
}
