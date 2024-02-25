<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Class StickersCategory
 *
 * @property int id
 * @property string name
 * @property int updated_at
 * @property int created_at
 */
class StickersCategory extends BaseModel
{
    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     */
    protected $guarded = [];
}
