@extends('layout')

@section('title')
    {{ $photo->title }} - Комментарии - @parent
@stop

@section('content')

    <h1>{{ $photo['title'] }}</h1>

    <i class="fa fa-picture-o"></i> <b><a href="/gallery/{{ $photo['id'] }}">К фото</a></b><hr>

    @if ($comments->isNotEmpty())
        @foreach ($comments as $data)
            <div class="post">
                <div class="b">
                    <div class="img">{!! user_avatars($data->user) !!}</div>
                    <div class="float-right">
                        @if (App::getUserId() != $data['user_id'])
                            <a href="#" onclick="return postReply(this)" title="Ответить"><i class="fa fa-reply text-muted"></i></a>

                            <a href="#" onclick="return postQuote(this)" title="Цитировать"><i class="fa fa-quote-right text-muted"></i></a>

                            <a href="#" onclick="return sendComplaint(this)" data-type="{{ Photo::class }}" data-id="{{ $data['id'] }}" data-token="{{ $_SESSION['token'] }}" data-page="{{ $page['current'] }}" rel="nofollow" title="Жалоба"><i class="fa fa-bell text-muted"></i></a>
                        @endif

                        @if ($data->user_id == App::getUserId() && $data['created_at'] + 600 > SITETIME)
                            <a title="Редактировать" href="/gallery/{{ $photo->id }}/{{ $data['id'] }}/edit?page={{ $page['current'] }}"><i class="fa fa-pencil text-muted"></i></a>
                        @endif

                        @if (is_admin())
                            <a href="#" onclick="return deleteComment(this)" data-rid="{{ $data['relate_id'] }}" data-id="{{ $data['id'] }}" data-type="{{ Photo::class }}" data-token="{{ $_SESSION['token'] }}" data-toggle="tooltip" title="Удалить"><i class="fa fa-remove text-muted"></i></a>
                        @endif
                    </div>

                    <b>{!! profile($data->user) !!}</b> <small>({{ date_fixed($data['created_at']) }})</small><br>
                    {!! user_title($data->user) !!} {!! user_online($data->user) !!}
                </div>
                <div class="message">
                    {!! App::bbCode($data['text']) !!}
                </div>

                @if (is_admin())
                    <span class="data">({{ $data['brow'] }}, {{ $data['ip'] }})</span>
                @endif
            </div>
        @endforeach

        {{ App::pagination($page) }}
    @endif

    @if (empty($photo['closed']))

        @if (empty($page['total']))
            {{ App::showError('Комментариев еще нет!') }}
        @endif

        @if (is_user())
            <div class="form">
                <form action="/gallery/{{ $photo->id }}/comments" method="post">
                    <input type="hidden" name="token" value="{{  $_SESSION['token'] }}">

                    <textarea id="markItUp" cols="25" rows="5" name="msg"></textarea><br>
                    <button class="btn btn-success">Написать</button>
                </form>
            </div><br>

            <a href="/rules">Правила</a> /
            <a href="/smiles">Смайлы</a> /
            <a href="/tags">Теги</a><br><br>
        @else
            {{ App::showError('Вы не авторизованы, чтобы добавить комментарий, необходимо') }}
        @endif
    @else
        {{ App::showError('Комментирование данной фотографии закрыто!') }}
    @endif

    <i class="fa fa-arrow-circle-up"></i> <a href="/gallery/album/{{ $photo->getUser()->login }}">Альбом</a><br>
    <i class="fa fa-arrow-circle-left"></i> <a href="/gallery">В галерею</a><br>
@stop
