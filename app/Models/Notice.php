<?php

namespace App\Models;

/**
 * Class Notice
 *
 * @property int id
 * @property string type
 * @property string name
 * @property string text
 * @property int user_id
 * @property int created_at
 * @property int updated_at
 * @property int protect
 */
class Notice extends BaseModel
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
