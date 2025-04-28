<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Class Search
 *
 * @property int id
 * @property string relate_type
 * @property int relate_id
 * @property string text
 * @property int created_at
 */
class Search extends BaseModel
{
    /**
     * The table associated with the model.
     */
    protected $table = 'search';

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     */
    protected $guarded = [];

    /**
     * Возвращает связанные объекты
     */
    public function relate(): MorphTo
    {
        return $this->morphTo('relate');
    }
}
