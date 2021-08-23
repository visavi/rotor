<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

/**
 * Class User
 *
 * @property int id
 * @property int user_id
 * @property int field_id
 * @property string value
 *
 * @property Collection<UserField> field
 */
class UserData extends BaseModel
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

    /**
     * Return field
     *
     * @return BelongsTo
     */
    public function field(): BelongsTo
    {
        return $this->belongsTo(UserField::class, 'field_id')->withDefault();
    }
}
