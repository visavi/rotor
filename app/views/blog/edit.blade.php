@extends('layout')

@section('title')
    Редактирование статьи - @parent
@stop

@section('content')

    <h1>Редактирование статьи</h1>

    <a href="/blog">Блоги</a> /
    <a href="/blog/search">Поиск</a> /
    <a href="/blog/blog?act=blogs">Все статьи</a><hr>

    <div class="form next">
        <form action="/article/{{ $blog['id'] }}?token={{ $_SESSION['token'] }}" method="post">

            Раздел:<br>
            <select name="cats">
                @foreach ($cats as $key => $value)
                    <?php $selected = ($blog['category_id'] == $key) ? ' selected="selected"' : ''; ?>
                    <option value="{{ $key }}"{{  $selected }}>{{ $value }}</option>
                @endforeach
            </select><br>

            Заголовок:<br>
            <input name="title" size="50" maxlength="50" value="{{ $blog['title'] }}"><br>
            Текст:<br>
            <textarea id="markItUp" cols="25" rows="15" name="text">{{ $blog['text'] }}</textarea><br>
            Метки:<br>
            <input name="tags" size="50" maxlength="100" value="{{ $blog['tags'] }}"><br>

            <button class="btn btn-primary">Изменить</button>
        </form>
    </div><br>

    <a href="/rules">Правила</a> /
    <a href="/smiles">Смайлы</a> /
    <a href="/tags">Теги</a><br><br>
    <?php
    App::view('includes/back', ['link' => '/article/' . $blog->id, 'title' => 'Вернуться']);
    App::view('includes/back', ['link' => '/blog', 'title' => 'К блогам', 'icon' => 'fa-arrow-circle-up']);
    ?>
@stop
