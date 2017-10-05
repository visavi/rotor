@extends('layout')

@section('title')
    Мои закладки
@stop

@section('content')
    <h1>Мои закладки</h1>

    <a href="/forum">Форум</a>

    @if ($page['total'] > 0)
        <form action="/forum/bookmark/delete?page={{ $page['current'] }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">
            @foreach ($topics as $topic)
                <div class="b">
                    <input type="checkbox" name="del[]" value="{{ $topic['id'] }}">

                    <i class="fa {{ $topic->topic->getIcon() }} text-muted"></i>
                    <b><a href="/topic/{{ $topic['id'] }}">{{ $topic['title'] }}</a></b>
                    ({{ $topic['posts'] }}{!! ($topic['posts'] > $topic['book_posts']) ? '/<span style="color:#00cc00">+' . ($topic['posts'] - $topic['book_posts']) . '</span>' : '' !!})
                </div>

                <div>
                    {{ $topic->topic->pagination() }}
                    Автор: {{ $topic->topic->user->login }} /
                    Посл.: {{ $topic->topic->lastPost->user->login }}
                    ({{ dateFixed($topic->topic->lastPost->created_at) }})
                </div>
            @endforeach

            <br>
            <input type="submit" value="Удалить выбранное">
        </form>

        {{ pagination($page) }}
    @else
        {{ showError('Закладок еще нет!') }}
    @endif
@stop
