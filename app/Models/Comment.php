<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Class Comment
 *
 * @property int id
 * @property int user_id
 * @property string relate_type
 * @property int relate_id
 * @property string text
 * @property string ip
 * @property string brow
 * @property int created_at
 */
class Comment extends BaseModel
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
     * Возвращает связанные объекты
     *
     * @return MorphTo
     */
    public function relate(): MorphTo
    {
        return $this->morphTo('relate');
    }

    /**
     * Morph name
     *
     * @var string
     */
    public static $morphName = 'comments';
}
