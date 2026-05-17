<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Tag
 *
 * @property int    $id
 * @property string $name
 */
class Tag extends Model
{
    public $timestamps = false;

    protected $guarded = [];
}
