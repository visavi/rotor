<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Class Login
 *
 * @property int id
 * @property int user_id
 * @property string ip
 * @property string brow
 * @property int created_at
 * @property int type
 */
class Login extends BaseModel
{
    public const AUTH = 'auth';
    public const COOKIE = 'cookie';
    public const SOCIAL = 'social';

    /**
     * The table associated with the model.
     */
    protected $table = 'login';

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     */
    protected $guarded = [];

    /**
     * Get type
     */
    public function getType(): string
    {
        return __('logins.' . $this->type);
    }
}
