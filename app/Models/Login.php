<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Login
 *
 * @property int    $id
 * @property int    $user_id
 * @property string $ip
 * @property string $brow
 * @property int    $created_at
 * @property int    $type
 */
class Login extends Model
{
    public const AUTH = 'auth';
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
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'user_id' => 'int',
        ];
    }

    /**
     * Возвращает связь пользователя
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withDefault();
    }

    /**
     * Get type
     */
    public function getType(): string
    {
        return __('logins.' . $this->type);
    }
}
