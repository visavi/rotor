<?php

return [
    'page_main'        => 'Interface description home page',
    'page_user'        => 'Returns the data of the current user. GET, Response: array of data from user profile ',
    'page_users'       => 'Returns the data of the selected user. GET, Parameters: login - User login, Reply: array of data from the user profile',
    'page_dialogues'   => 'Returns dialogues with users - GET, Parameters: page - page number, Response: array of dialogues',
    'page_messages'    => 'Returns private messages of the user - GET, Parameters: login - User login, page - page number, Reply: array of private messages',
    'page_forums'      => 'Returns messages from the topic in the forum - GET, Parameters: id, page - page number, Reply: array of forum topics',
    'page_topics'      => 'Returns posts from the topic in the forum - GET, Parameters: id, page - page number, Reply: array of posts',
    'text_description' => 'To access the data you need an API key, which can be obtained on the My Data page',
    'text_example'     => 'Usage example',
    'text_return'      => 'Returns json',
];
