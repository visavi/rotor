<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Support\Facades\Date;

/**
 * Class EmailChange
 *
 * @property int    $id
 * @property int    $user_id
 * @property string $email
 * @property string $token
 * @property Date   $created_at
 */
class EmailChange extends BaseModel
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
