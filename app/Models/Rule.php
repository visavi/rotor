<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Class Rule
 *
 * @property int    $id
 * @property string $text
 * @property int    $created_at
 */
class Rule extends BaseModel
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
