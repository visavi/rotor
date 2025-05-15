<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Class ArticleTag
 *
 * @property int $id
 * @property int $article_id
 * @property int $tag_id
 */
class ArticleTag extends BaseModel
{
    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     */
    protected $guarded = [];
}
