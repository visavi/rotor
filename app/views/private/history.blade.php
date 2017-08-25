@extends('layout')

@section('title')
    История переписки - @parent
@stop

@section('content')

    <h1>История переписки</h1>

    <i class="fa fa-envelope"></i> <a href="/private">Входящие</a> /
    <a href="/private/outbox">Отправленные</a> /
    <a href="/private/trash">Корзина</a>
    <hr>

    @if ($messages->isNotEmpty())

        @foreach($messages as $data)
            <div class="b">
                {!! user_avatars($data['author']) !!}
                <b>{!! profile($data['author']) !!}</b> {!! user_online($data['author']) !!}
                ({{  date_fixed($data['created_at']) }})
            </div>
            <div>{!! App::bbCode($data['text']) !!}</div>
        @endforeach

        {{ App::pagination($page) }}

    @else
        {{ show_error('История переписки отсутствует!') }}
    @endif

    <br>
    <div class="form">
        <form action="/private/send?user={{ $user->login }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <label for="markItUp">Сообщение:</label>
            <textarea class="form-control" id="markItUp" rows="5" name="msg" placeholder="Текст сообщения" required></textarea>


            @if (App::user('point') < Setting::get('privatprotect'))
                Проверочный код:<br>
                <img src="/captcha" alt=""><br>
                <input name="provkod" size="6" maxlength="6"><br>
            @endif

            <button class="btn btn-primary">Быстрый ответ</button></form></div><br>

            Всего писем: <b>{{ $page['total'] }}</b><br><br>

            <i class="fa fa-search"></i> <a href="/searchuser">Поиск контактов</a><br>
            <i class="fa fa-envelope"></i> <a href="/private/send">Написать письмо</a><br>
            <i class="fa fa-address-book"></i> <a href="/contact">Контакт</a> / <a href="/ignore">Игнор</a><br>

@stop
