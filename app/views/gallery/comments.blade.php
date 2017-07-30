@extends('layout')

@section('title')
    {{ $photo->title }} - Комментарии - @parent
@stop

@section('content')

    <h1>{{ $photo['title'] }}</h1>

    <i class="fa fa-picture-o"></i> <b><a href="/gallery/{{ $photo['id'] }}">К фото</a></b><hr />

    @if ($comments->isNotEmpty())
        @if ($isAdmin)
            <form action="/gallery/{{ $photo['id'] }}/delete" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">
        @endif

        @foreach ($comments as $data)
            <div class="b">
                <div class="img">{!! user_avatars($data->user) !!}</div>

                @if ($isAdmin)
                    <span class="imgright"><input type="checkbox" name="del[]" value="{{ $data['id'] }}" /></span>
                @endif

                <b>{!! profile($data->user) !!}</b> <small>({{ date_fixed($data['created_at']) }})</small><br />
                {!! user_title($data->user) !!} {!! user_online($data->user) !!}
            </div>

            @if ($data->user_id == App::getUserId() && $data['created_at'] + 600 > SITETIME)
                <div class="right">
                    <a href="/gallery?act=editcomm&amp;gid='.$photo->id.'&amp;cid='.$data['id'].'&amp;page='.$page['current'].'">Редактировать</a>
                </div>
            @endif

            <div>
                {!! App::bbCode($data['text']) !!}<br />

                @if ($isAdmin || empty(App::setting('anonymity')))
                    <span class="data">({{ $data['brow'] }}, {{ $data['ip'] }})</span>
                @endif
            </div>
        @endforeach

        @if ($isAdmin)
            <span class="imgright"><input type="submit" value="Удалить выбранное" /></span></form>
        @endif

        {{ App::pagination($page) }}
    @endif

    @if (empty($photo['closed']))

        @if (empty($page['total']))
            {{ show_error('Комментариев еще нет!') }}
        @endif

        @if (is_user())
            <div class="form">
                <form action="/gallery?act=addcomm&amp;gid={{ $photo->id }}" method="post">
                    <input type="hidden" name="token" value="{{  $_SESSION['token'] }}">

                    <textarea id="markItUp" cols="25" rows="5" name="msg"></textarea><br />
                    <input type="submit" value="Написать" />
                </form>
            </div><br />

            <a href="/rules">Правила</a> /
            <a href="/smiles">Смайлы</a> /
            <a href="/tags">Теги</a><br /><br />
        @else
            {{ show_login('Вы не авторизованы, чтобы добавить комментарий, необходимо') }}
        @endif
    @else
        {{ show_error('Комментирование данной фотографии закрыто!') }}
    @endif

    <i class="fa fa-arrow-circle-up"></i> <a href="/gallery/album?act=photo&amp;uz={{ $photo->getUser()->login }}">Альбом</a><br />
    <i class="fa fa-arrow-circle-left"></i> <a href="/gallery">В галерею</a><br />
@stop
