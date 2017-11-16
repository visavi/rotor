@extends('layout')

@section('title')
   {{ $offer->title }} - Комментарии
@stop

@section('content')

    <h1><a href="/offers/{{ $offer->id }}">{{ $offer->title }}</a> - Комментарии</h1>

    <i class="fa fa-book"></i>
    <a href="/offers/offer">Предложения</a> /
    <a href="/offers/issue">Проблемы</a>

    @if ($comments->isNotEmpty())
        @foreach ($comments as $data)
            <div class="post">
                <div class="b">
                    <div class="img">{!! userAvatar($data->user) !!}</div>

                    @if (getUser())
                        <div class="float-right">
                            @if (getUser('id') != $data->user_id)
                                <a href="#" onclick="return postReply(this)" title="Ответить"><i class="fa fa-reply text-muted"></i></a>

                                <a href="#" onclick="return postQuote(this)" title="Цитировать"><i class="fa fa-quote-right text-muted"></i></a>

                                <a href="#" onclick="return sendComplaint(this)" data-type="{{ App\Models\Offer::class }}" data-id="{{ $data->id }}" data-token="{{ $_SESSION['token'] }}" data-page="{{ $page['current'] }}" rel="nofollow" title="Жалоба"><i class="fa fa-bell text-muted"></i></a>
                            @endif

                            @if (getUser('id') == $data->user->id && $data->created_at + 600 > SITETIME)
                                <a href="/offers/{{ $offer->id }}/{{ $data->id }}/edit?page={{ $page['current'] }}"><i class="fa fa-pencil text-muted"></i></a>
                            @endif

                            @if (isAdmin())
                                <a href="#" onclick="return deleteComment(this)" data-rid="{{ $data->relate_id }}" data-id="{{ $data->id }}" data-type="{{ App\Models\Offer::class }}" data-token="{{ $_SESSION['token'] }}" data-toggle="tooltip" title="Удалить"><i class="fa fa-remove text-muted"></i></a>
                            @endif
                        </div>
                    @endif

                    <b>{!! profile($data->user) !!}</b> <small>({{ dateFixed($data->created_at) }})</small><br>
                    {!! userStatus($data->user) !!} {!! userOnline($data->user) !!}
                </div>
                <div class="message">
                    {!! bbCode($data->text) !!}<br>
                </div>

                @if (isAdmin())
                    <span class="data">({{ $data->brow }}, {{ $data->ip }})</span>
                @endif
            </div>
        @endforeach

        {!! pagination($page) !!}
    @else
        {{ showError('Нет сообщений') }}
    @endif

    @if (getUser())
        @if (empty($offer->closed))
            <div class="form">
                <form action="/offers/{{ $offer->id }}/comments" method="post">
                    <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

                    <div class="form-group{{ hasError('msg') }}">
                        <label for="markItUp">Сообщение:</label>
                        <textarea class="form-control" id="markItUp" rows="5" name="msg" required>{{ getInput('msg') }}</textarea>
                        {!! textError('msg') !!}
                    </div>

                    <button class="btn btn-success">Написать</button>
                </form>
            </div><br>

            <a href="/rules">Правила</a> /
            <a href="/smiles">Смайлы</a> /
            <a href="/tags">Теги</a><br><br>

        @else
            {{ showError('Комментирование данной записи закрыто!') }}
        @endif
    @else
        {{ showError('Для добавления сообщения необходимо авторизоваться') }}
    @endif

    <i class="fa fa-arrow-circle-up"></i> <a href="/offers">К записям</a><br>
    <i class="fa fa-arrow-circle-left"></i> <a href="/offers/{{ $offer->id }}">Вернуться</a><br>

@stop
