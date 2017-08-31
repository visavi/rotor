@extends('layout')

@section('title')
    Мои закладки - @parent
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

                    <i class="fa {{ $topic->getTopic()->getIcon() }} text-muted"></i>
                    <b><a href="/topic/{{ $topic['id'] }}">{{ $topic['title'] }}</a></b>
                    ({{ $topic['posts'] }}{!! ($topic['posts'] > $topic['book_posts']) ? '<span style="color:#00cc00">+ ' . ($topic['posts'] - $topic['book_posts']) . '</span>' : '' !!})
                </div>

                <div>
                    {{ Forum::pagination($topic) }}
                    Автор: {{ $topic->getTopic()->getUser()->login }} /
                    Посл.: {{ $topic->getTopic()->getLastPost()->getUser()->login }}
                    ({{ date_fixed($topic->getTopic()->getLastPost()->created_at) }})
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
