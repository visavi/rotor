<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Capsule\Manager as DB;

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
     * @param int $attempts кол. попыток
     *
     * @return bool
     */
    public function isFlood(int $attempts = 1): bool
    {
        self::query()->where('created_at', '<', SITETIME)->delete();

        $flood = self::query()
            ->where('uid', $this->getUid())
            ->where('page', request()->getPathInfo())
            ->first();

        if ($flood && $flood->attempts >= $attempts) {
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

        self::query()->updateOrCreate([
            'uid'  => $this->getUid(),
            'page' => request()->getPathInfo(),
        ], [
            'attempts'   => DB::connection()->raw('attempts + 1'),
            'created_at' => SITETIME + $period,
        ]);
    }

    /**
     * Get uid
     *
     * @return string
     */
    private function getUid(): string
    {
        return md5((string) (getUser('id') ?? getIp()));
    }
}
