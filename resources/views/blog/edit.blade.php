@extends('layout')

@section('title')
    Редактирование статьи
@stop

@section('content')

    <h1>Редактирование статьи</h1>

    <a href="/blog">Блоги</a> /
    <a href="/blog/search">Поиск</a> /
    <a href="/blog/blogs">Все статьи</a><hr>

    <div class="form next">
        <form action="/article/{{ $blog->id }}/edit" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">
            Раздел:<br>
            <select name="cid">
                @foreach ($cats as $key => $value)
                    <option value="{{ $key }}"{{ $blog->category_id == $key ? ' selected' : '' }}>{{ $value }}</option>
                @endforeach
            </select><br>

            <div class="form-group{{ hasError('title') }}">
                <label for="inputTitle">Заголовок:</label>
                <input type="text" class="form-control" id="inputTitle" name="title" maxlength="50" value="{{ getInput('title', $blog->title) }}" required>
                {!! textError('title') !!}
            </div>

            <div class="form-group{{ hasError('text') }}">
                <label for="markItUp">Текст:</label>
                <textarea class="form-control" id="markItUp" rows="5" name="text" required>{{ getInput('text', $blog->text) }}</textarea>
                {!! textError('text') !!}
            </div>

            <div class="form-group{{ hasError('tags') }}">
                <label for="inputTags">Метки:</label>
                <input type="text" class="form-control" id="inputTags" name="tags" maxlength="100" value="{{ getInput('tags', $blog->tags) }}" required>
                {!! textError('tags') !!}
            </div>

            <button class="btn btn-primary">Изменить</button>
        </form>
    </div><br>

    <a href="/rules">Правила</a> /
    <a href="/smiles">Смайлы</a> /
    <a href="/tags">Теги</a><br><br>

    <i class="fa fa-arrow-circle-up"></i> <a href="/blog">К блогам</a><br>
    <i class="fa fa-arrow-circle-left"></i> <a href="/article/{{ $blog->id }}">Вернуться</a><br>
@stop
