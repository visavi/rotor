<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Class Mailing
 *
 * @property int id
 * @property int user_id
 * @property string type
 * @property string subject
 * @property string text
 * @property int sent
 * @property int created_at
 * @property int sent_at
 */
class Mailing extends BaseModel
{
    /**
     * The table associated with the model.
     */
    protected $table = 'mailings';

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     */
    protected $guarded = [];
}
