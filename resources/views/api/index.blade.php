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
    <i class="fa fa-cog"></i> <b><a href="/api">api</a></b> - {{ __('api.page_main') }}<br>
    <i class="fa fa-cog"></i> <b><a href="/api/user">api/user</a></b> - {{ __('api.page_user') }}<br>
    <i class="fa fa-cog"></i> <b><a href="/api/users/admin">api/users/{login}</a></b> - {{ __('api.page_users') }}<br>
    <i class="fa fa-cog"></i> <b><a href="/api/dialogues">api/dialogues</a></b> {{ __('api.page_dialogues') }}<br>
    <i class="fa fa-cog"></i> <b><a href="/api/talk/admin">api/talk/{login}</a></b> {{ __('api.page_messages') }}<br>
    <i class="fa fa-cog"></i> <b><a href="/api/forums/1">api/forums/{id}</a></b> {{ __('api.page_forums') }}<br>
    <i class="fa fa-cog"></i> <b><a href="/api/topics/1">api/topics/{id}</a></b> {{ __('api.page_topics') }}<br>

    <br>{{ __('api.text_description') }}<br><br>

    {{ __('api.text_example') }}
<pre class="prettyprint linenums">
GET https://visavi.net/api/forums/1
{
  "token": "key",
  "page": 1
}

// {{ __('main.or') }}
/api/forums/1?token=key&amp;page=1
</pre>

    {{ __('api.text_return') }}
<pre class="prettyprint linenums">
{
  "login": "admin",
  "email": "my@domain.com",
  "name": "Alex",
  "country": "Russia",
  "city": "Moscow",
  "site": "https://visavi.net",
  "gender": "male",
  "birthday": "11.12.1981",
  "newwall": 0,
  "point": 8134,
  "money": 110675,
  "ban": 0,
  "allprivat": 1,
  "newprivat": 0,
  "status": "<span style=\"color:#ff0000\">Status</span>",
  "avatar": "",
  "picture": "",
  "rating": 567,
  "lastlogin": 1502102146
}
</pre>
@stop
