<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Class VoteAnswer
 *
 * @property int id
 * @property int vote_id
 * @property string answer
 * @property int result
 */
class VoteAnswer extends BaseModel
{
    /**
     * The table associated with the model.
     */
    protected $table = 'voteanswer';

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     */
    protected $guarded = ['id'];
}
