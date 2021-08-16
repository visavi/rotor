<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class User
 *
 * @property int id
 * @property string name
 * @property string value
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
