<?php

return [
    'page_main'        => 'Interface description home page',
    'page_users'       => 'Returns user data. GET: Options: token, Answer: array of data from user profile',
    'page_messages'    => 'Returns user private messages - GET: Options: token, count = 10, Answer: total - count posts, messages - array of private messages',
    'page_forums'      => 'Returns posts from a forum topic - GET: Options: token, id, Answer: id - id темы, author - topic author, title - topic title, messages - array of posts',
    'text_description' => 'To access the data you need an API key, which can be obtained on the My Data page',
    'text_example'     => 'Usage example',
    'text_return'      => 'Returns json',
];
