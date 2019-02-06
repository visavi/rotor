@extends('layout')

@section('title')
    {{ trans('api.title') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ trans('api.title') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <i class="fa fa-cog"></i> <b><a href="/api">api</a></b> - {{ trans('api.page_main') }}<br>
    <i class="fa fa-cog"></i> <b><a href="/api/users">api/users</a></b> - {{ trans('api.page_users') }}<br>
    <i class="fa fa-cog"></i> <b><a href="/api/messages">api/messages</a></b> {{ trans('api.page_messages') }}<br>
    <i class="fa fa-cog"></i> <b><a href="/api/forums">api/forums</a></b> {{ trans('api.page_forums') }}<br>

    <br>{{ trans('api.text_description') }}<br><br>

    {{ trans('api.text_example') }}
<pre class="prettyprint linenums">/api/users?token=key</pre>

    {{ trans('api.text_return') }}
<pre class="prettyprint linenums">
{
  "login": "admin",
  "email": "my@domain.com",
  "name": "Alex",
  "country": "Russia",
  "city": "Moscow",
  "site": "http://pizdec.ru",
  "icq": "364466",
  "skype": "vantuzilla",
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
