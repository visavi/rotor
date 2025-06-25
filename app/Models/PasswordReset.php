<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Support\Facades\Date;

/**
 * Class PasswordReset
 *
 * @property string $email
 * @property int    $token
 * @property Date   $created_at
 */
class PasswordReset extends BaseModel
{
    /**
     * The primary key for the model.
     */
    protected $primaryKey = 'email';

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = false;

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     */
    protected $guarded = [];
}
