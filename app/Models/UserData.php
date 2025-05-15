<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

/**
 * Class User
 *
 * @property int    $id
 * @property int    $user_id
 * @property int    $field_id
 * @property string $value
 * @property-read Collection<UserField> $field
 */
class UserData extends BaseModel
{
    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'value',
        'field_id',
    ];

    /**
     * Return field
     */
    public function field(): BelongsTo
    {
        return $this->belongsTo(UserField::class, 'field_id')->withDefault();
    }
}
