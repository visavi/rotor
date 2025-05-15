<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Class Reader
 *
 * @property int    $id
 * @property string $relate_type
 * @property int    $relate_id
 * @property string $ip
 * @property int    $created_at
 */
class Reader extends BaseModel
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
     * Counting stat
     */
    public static function countingStat(BaseModel $model): void
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
