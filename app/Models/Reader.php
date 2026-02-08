<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Reader
 *
 * @property int    $id
 * @property string $relate_type
 * @property int    $relate_id
 * @property string $ip
 * @property int    $created_at
 */
class Reader extends Model
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
     * Counting stat
     */
    public static function countingStat(Model $model): void
    {
        $reader = self::query()
            ->where('relate_type', $model->getMorphClass())
            ->where('relate_id', $model->id)
            ->where('ip', getIp())
            ->first();

        if (! $reader) {
            self::query()->create([
                'relate_type' => $model->getMorphClass(),
                'relate_id'   => $model->id,
                'ip'          => getIp(),
                'created_at'  => SITETIME,
            ]);

            $model->increment($model->countingField);
        }
    }
}
