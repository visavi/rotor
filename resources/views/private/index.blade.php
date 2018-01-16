@extends('layout')

@section('title')
    Приватные сообщения
@stop

@section('content')

    <h1>Приватные сообщения</h1>

    @if ($newprivat > 0)
        <div style="text-align:center"><b><span style="color:#ff0000">Получено новых писем: {{ getUser('newprivat') }}</span></b></div>
    @endif

    @if ($page['total'] >= (setting('limitmail') - (setting('limitmail') / 10)) && $page['total'] < setting('limitmail'))
        <div style="text-align:center"><b><span style="color:#ff0000">Ваш ящик почти заполнен, необходимо очистить или удалить старые сообщения!</span></b></div>
    @endif

    @if ($page['total'] >= setting('limitmail'))
        <div style="text-align:center"><b><span style="color:#ff0000">Ваш ящик переполнен, вы не сможете получать письма пока не очистите его!</span></b></div>
    @endif

    <i class="fa fa-envelope"></i> <b>Входящие ({{ $page['total'] }})</b> /
    <a href="/private/outbox">Отправленные ({{  $page['totalOutbox'] }})</a>
    <hr>

    @if ($messages->isNotEmpty())

        <form action="/private/delete?page={{ $page['current'] }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">
            <div class="form text-right">
                <label for="all">Отметить все</label>
                <input type="checkbox" id="all" onchange="var o=this.form.elements;for(var i=0;i&lt;o.length;i++)o[i].checked=this.checked">
            </div>

            @foreach ($messages as $data)
                <div class="b">
                    <div class="float-right">
                        @if ($data->author->id)
                            <a href="/private/send?user={{ $data->author->login }}" title="Ответить"><i class="fa fa-reply text-muted"></i></a>
                            <a href="/private/history?user={{ $data->author->login }}" title="История"><i class="fa fa-history text-muted"></i></a>
                            <a href="#" onclick="return sendComplaint(this)" data-type="{{ App\Models\Inbox::class }} " data-id="{{ $data->id }}" data-token="{{ $_SESSION['token'] }}" rel="nofollow" title="Жалоба"><i class="fa fa-bell text-muted"></i></a>
                        @endif

                        <input type="checkbox" name="del[]" value="{{ $data->id }}">
                    </div>

                    <div class="img">{!! userAvatar($data->author) !!}</div>
                    @if ($data->author->id)
                        <b>{!! profile($data->author) !!}</b> ({{ dateFixed($data->created_at) }})<br>
                        {!! userStatus($data->author) !!} {!! userOnline($data->author) !!}
                    @else
                        <b>Система</b>
                    @endif
                </div>
                <div>{!! bbCode($data->text) !!}</div>
            @endforeach

            <div class="float-right">
                <button class="btn btn-sm btn-danger">Удалить выбранное</button>
            </div>
        </form>

        {!! pagination($page) !!}

        Всего писем: <b>{{ $page['total'] }}</b><br>
        Объем ящика: <b>{{ setting('limitmail') }}</b><br><br>

        <i class="fa fa-times"></i> <a href="/private/clear?token={{ $_SESSION['token'] }}">Очистить ящик</a><br>
    @else
        {!! showError('Входящих писем еще нет!') !!}
    @endif

    <i class="fa fa-search"></i> <a href="/searchuser">Поиск контактов</a><br>
    <i class="fa fa-envelope"></i> <a href="/private/send">Написать письмо</a><br>
    <i class="fa fa-address-book"></i> <a href="/contact">Контакт</a> / <a href="/ignore">Игнор</a><br>
@stop
