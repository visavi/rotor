<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Class BlackList
 *
 * @property int    $id
 * @property string $type
 * @property string $value
 * @property int    $user_id
 * @property int    $created_at
 */
class BlackList extends BaseModel
{
    /**
     * The table associated with the model.
     */
    protected $table = 'blacklist';

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     */
    protected $guarded = [];
}
