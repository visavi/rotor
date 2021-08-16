<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Class User
 *
 * @property int id
 * @property int user_id
 * @property int field_id
 * @property string value
 */
class UserField extends BaseModel
{
    /**
     * Type fields
     */
    public const INPUT    = 'input';
    public const TEXTAREA = 'textarea';

    /**
     * All types
     */
    public const TYPES = [
        self::INPUT,
        self::TEXTAREA,
    ];

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
