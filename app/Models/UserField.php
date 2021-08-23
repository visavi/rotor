<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * Class User
 *
 * @property int id
 * @property int sort
 * @property string type
 * @property string name
 * @property int length
 * @property bool required
 *
 * @property Collection<UserData> data
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
/*    protected $guarded = [];*/

    protected $fillable = [
        'sort',
        'type',
        'name',
        'length',
        'required',
    ];

    /**
     * Возвращает данные
     *
     * @return HasMany
     */
    public function data(): HasMany
    {
        return $this->hasMany(UserData::class, 'field_id');
    }
}
