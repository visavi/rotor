<?php

namespace App\Models;

/**
 * Class Module
 *
 * @property int id
 * @property string version
 * @property string name
 * @property int updated_at
 * @property int created_at
 */
class Module extends BaseModel
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
