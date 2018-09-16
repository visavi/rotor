<?php

namespace App\Models;

/**
 * Class Polling
 *
 * @property int id
 * @property string relate_type
 * @property int relate_id
 * @property int user_id
 * @property string vote
 * @property int created_at
 */
class Polling extends BaseModel
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
}
