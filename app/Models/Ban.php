<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Class Ban
 *
 * @property int id
 * @property string ip
 * @property int user_id
 * @property int created_at
 */
class Ban extends BaseModel
{
    /**
     * The table associated with the model.
     */
    protected $table = 'ban';

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     */
    protected $guarded = [];
}
