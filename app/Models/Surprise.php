<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Class Surprise
 *
 * @property int $id
 * @property int $user_id
 * @property int $year
 * @property int $created_at
 */
class Surprise extends BaseModel
{
    /**
     * The table associated with the model.
     */
    protected $table = 'surprise';

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     */
    protected $guarded = [];
}
