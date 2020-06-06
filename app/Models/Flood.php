<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Class Flood
 *
 * @property int id
 * @property int user_id
 * @property string page
 * @property int created_at
 */
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
    public function getPeriod(): int
    {
        if (isAdmin()) {
            return 0;
        }

        $userPoint = getUser('point');
        $period    = setting('floodstime');

        if ($userPoint >= 100) {
            $period = round(setting('floodstime') / 2);
        }

        if ($userPoint >= 300) {
            $period = round(setting('floodstime') / 3);
        }

        if ($userPoint >= 500) {
            $period = round(setting('floodstime') / 6);
        }

        return (int) $period;
    }

    /**
     * Проверяет сообщение на флуд
     *
     * @return bool
     */
    public function isFlood(): bool
    {
        self::query()->where('created_at', '<', SITETIME)->delete();

        $flood = self::query()
            ->where('user_id', getUser('id'))
            ->where('page', request()->getPathInfo())
            ->exists();

        if ($flood) {
            return true;
        }

        return false;
    }

    /**
     * Сохраняет состояние
     *
     * @param int $period
     * @return void
     */
    public function saveState(int $period = 0): void
    {
        $period = $period ?: $this->getPeriod();

        if (empty($period)) {
            return;
        }

        self::query()->create([
            'user_id'    => getUser('id'),
            'page'       => request()->getPathInfo(),
            'created_at' => SITETIME + $period,
        ]);
    }
}
