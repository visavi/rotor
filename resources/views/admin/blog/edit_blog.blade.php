@extends('layout')

@section('title')
    Редактирование статьи
@stop

@section('content')

    <h1>Редактирование статьи</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">Панель</a></li>
            <li class="breadcrumb-item"><a href="/admin/blog">Блоги</a></li>

            @if ($blog->category->parent->id)
                <li class="breadcrumb-item"><a href="/admin/blog/{{ $blog->category->parent->id }}">{{ $blog->category->parent->name }}</a></li>
            @endif

            <li class="breadcrumb-item"><a href="/admin/blog/{{ $blog->category->id }}">{{ $blog->category->name }}</a></li>
            <li class="breadcrumb-item"><a href="/article/{{ $blog->id }}">{{ $blog->title }}</a></li>
            <li class="breadcrumb-item active">Редактирование статьи</li>
        </ol>
    </nav>

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
    </div>
@stop
