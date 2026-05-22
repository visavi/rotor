<?php

use Modules\Forum\Models\Post;
use Modules\Forum\Models\Topic;
use Modules\Forum\Models\Vote;

return [
    'name'        => 'Форум',
    'description' => 'Форум с темами и сообщениями',
    'version'     => '1.0.0',
    'requires'    => '14.0.0',
    'author'      => 'Vantuz',
    'email'       => 'admin@visavi.net',
    'homepage'    => 'https://visavi.net',

    'morph' => Topic::class,

    'morphs' => [
        Post::class,
        Vote::class,
    ],

    'search' => [
        'label' => __('forum::forums.topics'),
        'view'  => 'forum::search/_topics',
    ],

    'feed' => [
        'withs' => ['lastPost.user', 'lastPost.files', 'forum.parent'],
        'view'  => 'forum::feeds/_topics',
    ],

    'upload' => 'file',

    'rating' => true,

    'panel' => [
        '/admin/forums'         => __('index.forums'),
        '/admin/forum-settings' => __('forum::forums.settings'),
    ],
];
