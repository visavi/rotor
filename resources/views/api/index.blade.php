@extends('layout')

@section('title', __('index.api_interface'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ __('index.api_interface') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="mb-3 pb-3 border-bottom">
        <h3><i class="fa fa-cog"></i> api</h3>
        {{ __('api.page_main') }}
    </div>

    <div class="mb-3 pb-3 border-bottom">
    <h3><i class="fa fa-cog"></i> /api/user</h3>
    {{ __('api.page_user') }}<br>

<?= bbCode('[spoiler=' . __('api.request') . '][code]
GET https://visavi.net/api/user
{
  "token": "key",
}
[/code][/spoiler]') ?>

<?= bbCode('[spoiler=' . __('api.response') . '][code]
{
    "data": {
        "login": "Vantuz",
        "name": "Вантуз-мен",
        "level": "boss",
        "country": "Россия",
        "city": "Москва",
        "info": "Информация о пользователе",
        "site": "http://pizdec.ru",
        "gender": "male",
        "birthday": "11.12.1981",
        "visits": 52212,
        "allforum": 6752,
        "allguest": 433,
        "allcomments": 804,
        "themes": "default",
        "point": 10913,
        "money": 142399065,
        "status": "<span style=\"color:#ff0000\">Господин ПЖ</span>",
        "color": "#a723b8",
        "avatar": "https://visavi.net/uploads/avatars/6855b98a1c585599310878.png",
        "picture": "https://visavi.net/uploads/pictures/6855b98a1cb91672679465.jpg",
        "rating": 620,
        "language": "ru",
        "timezone": "0",
        "lastlogin": "2026-03-01T15:31:31+03:00",
        "email": "email@example.com",
        "phone": "+79999999999",
        "allprivat": 100,
        "newprivat": 1,
        "newwall": 0
    }
}
[/code][/spoiler]') ?>
    </div>


    <div class="mb-3 pb-3 border-bottom">
        <h3><i class="fa fa-cog"></i> /api/users/{login}</h3>
        {{ __('api.page_users') }}<br>

<?= bbCode('[spoiler=' . __('api.request') . '][code]
GET https://visavi.net/api/users/3v
{
  "token": "key",
}
[/code][/spoiler]') ?>

<?= bbCode('[spoiler=' . __('api.response') . '][code]
{
    "data": {
        "login": "3v",
        "name": "Vavan",
        "level": "user",
        "country": "",
        "city": "оттуда",
        "info": "Многие думают, но это не так.",
        "site": "",
        "gender": "male",
        "birthday": "09.05.1945",
        "visits": 9802,
        "allforum": 8252,
        "allguest": 24,
        "allcomments": 107,
        "themes": "default",
        "point": 8648,
        "money": 626999,
        "status": "<span style=\"color:#ff0000\">3v</span>",
        "color": null,
        "avatar": "https://visavi.net/uploads/avatars/5b0453ff8a30f214385566.png",
        "picture": "https://visavi.net/uploads/pictures/5b0453ff8290e940016027.jpg",
        "rating": 129,
        "language": "ru",
        "timezone": "0",
        "lastlogin": "2023-12-28T08:04:13+03:00"
    }
}
[/code][/spoiler]') ?>
    </div>

    <div class="mb-3 pb-3 border-bottom">
        <h3><i class="fa fa-cog"></i> /api/dialogues</h3>
        {{ __('api.page_dialogues') }}<br>

<?= bbCode('[spoiler=' . __('api.request') . '][code]
GET https://visavi.net/api/dialogues
{
  "token": "key",
  "per_page": 10,
  "page": 1
}
[/code][/spoiler]') ?>

<?= bbCode('[spoiler=' . __('api.response') . '][code]
{
    "data": [
        {
            "id": 228108,
            "login": "XaOS",
            "name": "XaOS",
            "text": "Привет, как дела?",
            "type": "out",
            "all_reading": true,
            "recipient_read": false,
            "created_at": "2026-03-01T03:48:14+03:00"
        },
        {
            "id": 228103,
            "login": 0,
            "name": "Система",
            "text": "Ваша статья <strong><a href=\"/url\">Статья</a></strong> снята с публикации",
            "type": "in",
            "all_reading": true,
            "recipient_read": false,
            "created_at": "2025-08-11T08:38:11+03:00"
        }
    ],
    "links": {
        // Список страниц
    },
    "meta": {
        // Информация о пагинации
    }
}
[/code][/spoiler]') ?>
    </div>

    <div class="mb-3 pb-3 border-bottom">
        <h3><i class="fa fa-cog"></i> /api/talk/{login}</h3>
        {{ __('api.page_messages') }}<br>

<?= bbCode('[spoiler=' . __('api.request') . '][code]
GET https://visavi.net/api/talk/XaOS
{
  "token": "key",
  "per_page": 10,
  "page": 1
}
[/code][/spoiler]') ?>

<?= bbCode('[spoiler=' . __('api.response') . '][code]
{
    "data": [
        {
            "id": 185757,
            "login": "Vantuz",
            "name": "Вантуз-мен",
            "text": "Привет, как дела?",
            "type": "out",
            "created_at": "2026-03-01T03:48:14+03:00",
            "files": [
                {
                    "id": 8353,
                    "name": "photo_2026-01-15_21-55-09.jpg",
                    "path": "https:/visavi.net/uploads/messages/hash.jpg",
                    "size": 47661,
                    "extension": "jpg",
                    "mime_type": "image/jpeg",
                    "is_image": true,
                    "is_audio": false,
                    "is_video": false
                }
            ]
        },
        {
            "id": 185668,
            "login": "XaOS",
            "name": "XaOS",
            "text": "Привет. Хорошо",
            "type": "in",
            "created_at": "2025-01-16T11:43:14+03:00",
            "files": []
        }
    ],
    "links": {
        // Список страниц
    },
    "meta": {
        // Информация о пагинации
    }
}
[/code][/spoiler]') ?>
    </div>

    <div class="mb-3 pb-3 border-bottom">
        <h3><i class="fa fa-cog"></i> /api/forums</h3>
        {{ __('api.page_category_forums') }}<br>

<?= bbCode('[spoiler=' . __('api.request') . '][code]
GET https://visavi.net/api/forums
{
  "token": "key"
}
[/code][/spoiler]') ?>

<?= bbCode('[spoiler=' . __('api.response') . '][code]
{
    "data": [
        {
            "id": 1,
            "parent_id": 0,
            "sort": 1,
            "title": "Общение",
            "description": "Описание раздела",
            "closed": false,
            "count_topics": 7473,
            "count_posts": 258020,
            "last_topic_id": 44907,
            "last_topic_title": "Последняя тема в форуме",
            "last_post_user_login": "Vantuz",
            "last_post_at": "2026-03-01T03:45:21+03:00",
            "children": [
                {
                    "id": 34,
                    "parent_id": 1,
                    "sort": 94,
                    "title": "Обзор сервисов",
                    "description": "Описание подраздела",
                    "closed": false,
                    "count_topics": 89,
                    "count_posts": 1269,
                    "last_topic_id": 44872,
                    "last_topic_title": "Путь заработка 70$ в сутки",
                    "last_post_user_login": "Randy",
                    "last_post_at": "2024-07-10T04:42:53+03:00"
                }
            ]
        }
    ]
}
[/code][/spoiler]') ?>
    </div>

    <div class="mb-3 pb-3 border-bottom">
        <h3><i class="fa fa-cog"></i> /api/forums/{id}</h3>
        {{ __('api.page_forums') }}<br>

<?= bbCode('[spoiler=' . __('api.request') . '][code]
GET https://visavi.net/api/forums/1
{
  "token": "key",
  "per_page": 10,
  "page": 1
}
[/code][/spoiler]') ?>

<?= bbCode('[spoiler=' . __('api.response') . '][code]
{
    "data": [
        {
            "id": 39200,
            "title": "Круглосуточное общение2",
            "login": "antimalish",
            "closed": 0,
            "locked": 1,
            "count_posts": 1151,
            "visits": 143548,
            "moderators": "Vantuz",
            "note": "Общение без ограничений!",
            "last_post_id": 711742,
            "last_post_user_login": "CHILI",
            "close_user_id": null,
            "updated_at": "2025-05-15T00:29:57+03:00",
            "created_at": "2013-10-14T04:09:38+04:00"
        },
        {
            "id": 42745,
            "title": "Старая гвардия",
            "login": "kolabas",
            "closed": 0,
            "locked": 1,
            "count_posts": 890,
            "visits": 8019,
            "moderators": "",
            "note": "",
            "last_post_id": 714536,
            "last_post_user_login": "Amney",
            "close_user_id": null,
            "updated_at": "2024-10-28T10:59:30+03:00",
            "created_at": "2016-01-06T03:07:17+03:00"
        }
    ],
    "links": {
        // Список страниц
    },
    "meta": {
        // Информация о пагинации
    }
}
[/code][/spoiler]') ?>
    </div>

    <div class="mb-3 pb-3 border-bottom">
        <h3><i class="fa fa-cog"></i> /api/topics/{id}</h3>
        {{ __('api.page_topics') }}<br>

<?= bbCode('[spoiler=' . __('api.request') . '][code]
GET https://visavi.net/api/topics/1
{
  "token": "key",
  "per_page": 10,
  "page": 1
}
[/code][/spoiler]') ?>

<?= bbCode('[spoiler=' . __('api.response') . '][code]
{
    "data": [
        {
            "id": 714727,
            "login": "Vantuz",
            "text": "Текст сообщения 1 <br><strong>Жирный текст</strong>",
            "rating": 0,
            "updated_at": "2026-02-23T04:01:46+03:00",
            "created_at": "2026-02-23T02:30:18+03:00"
            "files": []
        },
        {
            "id": 714728,
            "login": "Vantuz",
            "text": "Текст сообщения 2",
            "rating": 0,
            "updated_at": "2026-03-01T16:16:42+03:00",
            "created_at": "2026-03-01T03:45:21+03:00"
            "files": [
                {
                    "id": 8352,
                    "name": "image.jpg",
                    "path": "https:/visavi.net/uploads/forums/hash.jpg",
                    "size": 70904,
                    "extension": "jpg",
                    "mime_type": "image/jpeg",
                    "is_image": true,
                    "is_audio": false,
                    "is_video": false
                }
            ]
        }
    ],
    "links": {
        // Список страниц
    },
    "meta": {
        // Информация о пагинации
    }
}
[/code][/spoiler]') ?>
    </div>

    <br>{{ __('api.text_description') }}<br><br>
@stop
