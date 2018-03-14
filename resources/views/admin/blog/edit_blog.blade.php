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
        <form action="/admin/article/edit/{{ $blog->id }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('title') }}">
                <label for="inputTitle">Заголовок:</label>
                <input type="text" class="form-control" id="inputTitle" name="title" maxlength="50" value="{{ getInput('title', $blog->title) }}" required>
                {!! textError('title') !!}
            </div>

            <div class="form-group{{ hasError('text') }}">
                <label for="text">Текст:</label>
                <textarea class="form-control markItUp" id="text" rows="5" name="text" required>{{ getInput('text', $blog->text) }}</textarea>
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

    <i class="fa fa-arrow-circle-up"></i> <a href="/admin/blog">К блогам</a><br>
    <i class="fa fa-arrow-circle-left"></i> <a href="/admin/blog/{{ $blog->category_id }}">Вернуться</a><br>
@stop
