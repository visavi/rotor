@extends('layout')

@section('title')
    Редактирование статьи
@stop

@section('content')

    <h1>Редактирование статьи</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/blog">Блоги</a></li>

            @if ($blog->category->parent->id)
                <li class="breadcrumb-item"><a href="/blog/{{ $blog->category->parent->id }}">{{ $blog->category->parent->name }}</a></li>
            @endif

            <li class="breadcrumb-item"><a href="/blog/{{ $blog->category->id }}">{{ $blog->category->name }}</a></li>
            <li class="breadcrumb-item"><a href="/article/{{ $blog->id }}">{{ $blog->title }}</a></li>
            <li class="breadcrumb-item active">Редактирование</li>
        </ol>
    </nav>

    <div class="form next">
        <form action="/article/edit/{{ $blog->id }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('cid') }}">
                <label for="inputCategory">Раздел</label>

                <?php $inputCategory = getInput('cid', $blog->category_id); ?>
                <select class="form-control" id="inputCategory" name="cid">

                    @foreach ($categories as $data)
                        <option value="{{ $data->id }}"{{ ($inputCategory == $data->id && ! $data->closed) ? ' selected' : '' }}{{ $data->closed ? ' disabled' : '' }}>{{ $data->name }}</option>

                        @if ($data->children->isNotEmpty())
                            @foreach($data->children as $datasub)
                                <option value="{{ $datasub->id }}"{{ ($inputCategory == $datasub->id && ! $data->closed) ? ' selected' : '' }}{{ $datasub->closed ? ' disabled' : '' }}>– {{ $datasub->name }}</option>
                            @endforeach
                        @endif
                    @endforeach

                </select>
                {!! textError('cid') !!}
            </div>

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

    <a href="/rules">Правила</a> /
    <a href="/smiles">Смайлы</a> /
    <a href="/tags">Теги</a><br><br>
@stop
