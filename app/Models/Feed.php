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
        'topics'   => ['class' => Topic::class,   'withs' => ['lastPost.user', 'lastPost.files', 'forum.parent']],
        'news'     => ['class' => News::class,    'withs' => ['user', 'files']],
        'photos'   => ['class' => Photo::class,   'withs' => ['user', 'files']],
        'downs'    => ['class' => Down::class,    'withs' => ['user', 'files', 'category.parent']],
        'comments' => ['class' => Comment::class, 'withs' => ['relate', 'user']],
    ];

    public static array $viewMap = [];

    public $timestamps = false;

    protected $guarded = [];
}
