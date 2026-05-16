<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Feed
 *
 * @property int    $id
 * @property string $relate_type
 * @property int    $relate_id
 * @property int    $created_at
 */
class Feed extends Model
{
    public static array $types = [
        'topics'   => ['class' => \App\Models\Topic::class,   'withs' => ['lastPost.user', 'lastPost.files', 'forum.parent']],
        'news'     => ['class' => \App\Models\News::class,     'withs' => ['user', 'files']],
        'photos'   => ['class' => \App\Models\Photo::class,    'withs' => ['user', 'files']],
        'articles' => ['class' => \App\Models\Article::class,  'withs' => ['user', 'files', 'category.parent']],
        'downs'    => ['class' => \App\Models\Down::class,     'withs' => ['user', 'files', 'category.parent']],
        'offers'   => ['class' => \App\Models\Offer::class,    'withs' => ['user']],
        'comments' => ['class' => \App\Models\Comment::class,  'withs' => ['relate', 'user']],
    ];

    public static array $viewMap = [];

    public $timestamps = false;

    protected $guarded = [];
}
