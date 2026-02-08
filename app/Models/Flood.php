<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Flood
 *
 * @property int    $id
 * @property int    $user_id
 * @property string $page
 * @property int    $created_at
 */
class Flood extends Model
{
    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
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
     * Определяет время антифлуда
     */
    public function getPeriod(): int
    {
        if (isAdmin()) {
            return 0;
        }

        $userPoint = getUser('point');
        $period = setting('floodstime');

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
            'created_at' => SITETIME + $period,
        ])->increment('attempts');
    }

    /**
     * Get uid
     */
    private function getUid(): string
    {
        return md5((string) (getUser('id') ?? getIp()));
    }
}
