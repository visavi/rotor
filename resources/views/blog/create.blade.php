@extends('layout')

@section('title')
    Публикация новой статьи
@stop

@section('content')

    <h1>Публикация новой статьи</h1>

    <a href="/blog">Блоги</a> /
    <a href="/blog/search">Поиск</a> /
    <a href="/blog/blogs">Все статьи</a><hr>

    <div class="form next">
        <form action="/blog/create" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('cid') }}">
                <label for="inputCategory">Категория</label>
                <select class="form-control" id="inputCategory" name="cid">
                    <option value="0">Выберите категорию</option>
                    @foreach ($cats as $key => $value)
                        <option value="{{ $key }}"{{ $cid == $key ? ' selected' : '' }}>{{ $value }}</option>
                    @endforeach

                </select>
                {!! textError('cid') !!}
            </div>

            <div class="form-group{{ hasError('title') }}">
                <label for="inputTitle">Заголовок:</label>
                <input type="text" class="form-control" id="inputTitle" name="title" maxlength="50" value="{{ getInput('title') }}" required>
                {!! textError('title') !!}
            </div>

            <div class="form-group{{ hasError('text') }}">
                <label for="markItUp">Текст:</label>
                <textarea class="form-control" id="markItUp" rows="5" name="text" required>{{ getInput('text') }}</textarea>
                {!! textError('text') !!}
            </div>

            <div class="form-group{{ hasError('tags') }}">
                <label for="inputTags">Метки:</label>
                <input type="text" class="form-control" id="inputTags" name="tags" maxlength="100" value="{{ getInput('tags') }}" required>
                {!! textError('tags') !!}
            </div>

            <button class="btn btn-primary">Опубликовать</button>
        </form>
    </div><br>

    Рекомендация! Для разбивки статьи по страницам используйте тег [nextpage]<br>
    Метки статьи должны быть от 2 до 20 символов с общей длиной не более 50 символов<br><br>

    <a href="/rules">Правила</a> /
    <a href="/smiles">Смайлы</a> /
    <a href="/tags">Теги</a><br><br>
@stop
