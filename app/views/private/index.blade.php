@extends('layout')

@section('title')
    Приватные сообщения - @parent
@stop

@section('content')

    <h1>Приватные сообщения</h1>

    @if ($newprivat > 0)
        <div style="text-align:center"><b><span style="color:#ff0000">Получено новых писем: {{ App::user('newprivat') }}</span></b></div>
    @endif

    @if ($page['total'] >= (Setting::get('limitmail') - (Setting::get('limitmail') / 10)) && $page['total'] < Setting::get('limitmail'))
        <div style="text-align:center"><b><span style="color:#ff0000">Ваш ящик почти заполнен, необходимо очистить или удалить старые сообщения!</span></b></div>
    @endif

    @if ($page['total'] >= Setting::get('limitmail'))
        <div style="text-align:center"><b><span style="color:#ff0000">Ваш ящик переполнен, вы не сможете получать письма пока не очистите его!</span></b></div>
    @endif

    <i class="fa fa-envelope"></i> <b>Входящие ({{ $page['total'] }})</b> /
    <a href="/private/outbox">Отправленные ({{  $page['totalOutbox'] }})</a> /
    <a href="/private/trash">Корзина ({{ $page['totalTrash'] }})</a>
    <hr>

    @if ($messages->isNotEmpty())

        <form action="/private/delete?page={{ $page['current'] }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">
            <div class="form">
                <input type="checkbox" id="all" onchange="var o=this.form.elements;for(var i=0;i&lt;o.length;i++)o[i].checked=this.checked">
                <b><label for="all">Отметить все</label></b>
            </div>

            @foreach ($messages as $data)

                <div class="b">
                    <div class="img">{!! user_avatars($data->author) !!}</div>
                    @if ($data->author)
                        <b>{!! profile($data->author) !!}</b> ({{ date_fixed($data['created_at']) }})<br>
                        {!! user_title($data->author) !!} {!! user_online($data->author) !!}
                    @else
                        <b>Система</b>
                    @endif

                </div>
                <div>{!! App::bbCode($data['text']) !!}<br>

                    <input type="checkbox" name="del[]" value="{{ $data['id'] }}">

                    @if ($data->author)

                        <a href="/private/send?user={{ $data->getAuthor()->login }}">Ответить</a> /
                        <a href="/private/history?user={{ $data->getAuthor()->login }}">История</a> /
                        <a href="/contact?act=add&amp;uz={{ $data->getAuthor()->login }}&amp;token={{ $_SESSION['token'] }}">В контакт</a> /
                        <a href="/ignore?act=add&amp;uz={{ $data->getAuthor()->login }}&amp;token={{ $_SESSION['token'] }}">Игнор</a>
                        /

                        <a href="#" onclick="return sendComplaint(this)" data-type="{{ Inbox::class }} " data-id="{{ $data['id'] }}" data-token="{{ $_SESSION['token'] }}" rel="nofollow" title="Жалоба"><i class="fa fa-bell text-muted"></i></a>
                    @endif

                </div>
            @endforeach

            <br><input type="submit" value="Удалить выбранное"></form>

        {{ App::pagination($page) }}

        Всего писем: <b>{{ $page['total'] }}</b><br>
        Объем ящика: <b>{{ Setting::get('limitmail') }}</b><br><br>

        <i class="fa fa-times"></i> <a href="/private/clear?token={{ $_SESSION['token'] }}">Очистить ящик</a><br>
    @else
        {{ show_error('Входящих писем еще нет!') }}
    @endif

    <i class="fa fa-search"></i> <a href="/searchuser">Поиск контактов</a><br>
    <i class="fa fa-envelope"></i> <a href="/private/send">Написать письмо</a><br>
    <i class="fa fa-address-book"></i> <a href="/contact">Контакт</a> / <a href="/ignore">Игнор</a><br>

@stop
