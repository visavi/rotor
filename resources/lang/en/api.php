<?php

return [
    'page_main'            => 'Interface description home page',
    'page_user'            => 'Returns the data of the current user. GET, Response: array of data from user profile ',
    'page_users'           => 'Returns the data of the selected user. GET, Parameters: login - User login, Response: array of data from the user profile',
    'page_dialogues'       => 'Returns dialogues with users - GET, Parameters: page - page number, Response: array of dialogues',
    'page_messages'        => 'Returns private messages of the user - GET, Parameters: login - User login, page - page number, Response: array of private messages',
    'page_category_forums' => 'Returns forum categories - GET, Response: array of forum categories',
    'page_forums'          => 'Returns forum topics from a category - GET, Parameters: id, page - page number, Response: array of forum topics',
    'page_topics'          => 'Returns posts from the topic in the forum - GET, Parameters: id, page - page number, Response: array of posts',
    'text_description'     => 'To access the data you need an API key, which can be obtained on the My Data page',
    'request'              => 'Request',
    'response'             => 'Response',
];
