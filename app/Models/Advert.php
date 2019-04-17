<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Class Advert
 *
 * @property int id
 * @property string site
 * @property string name
 * @property string color
 * @property int bold
 * @property int user_id
 * @property int created_at
 * @property int deleted_at
 */
class Advert extends BaseModel
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
