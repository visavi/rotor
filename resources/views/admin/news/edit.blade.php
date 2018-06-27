@extends('layout')

@section('title')
    Редактирование новости
@stop

@section('content')

    <h1>Редактирование новости</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">Панель</a></li>
            <li class="breadcrumb-item"><a href="/admin/news">Новости</a></li>
            <li class="breadcrumb-item"><a href="/news/{{ $news->id }}">{{ $news->title }}</a></li>
            <li class="breadcrumb-item active">Редактирование новости</li>
        </ol>
    </nav>

    <div class="form cut">
        <form action="/admin/news/edit/{{ $news->id }}?page={{ $page }}" method="post" enctype="multipart/form-data">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('title') }}">
                <label for="title">Заголовок:</label>
                <input type="text" class="form-control" id="title" name="title" maxlength="100" value="{{ getInput('title', $news->title) }}" required>
                {!! textError('title') !!}
            </div>

            <div class="form-group{{ hasError('text') }}">
                <label for="text">Текст:</label>
                <textarea class="form-control markItUp" id="text" rows="10" name="text" required>{{ getInput('text', $news->text) }}</textarea>
                {!! textError('text') !!}
            </div>

            @if ($news->image && file_exists(HOME . $news->image))

                <a href="{{ $news->image }}">{!! resizeImage($news->image, ['width' => 100, 'alt' => $news['title']]) !!}</a><br>
                <b>{{ $news->image }}</b> ({{ formatFileSize(HOME . $news->image) }})<br><br>
            @endif

            <label class="btn btn-sm btn-secondary" for="image">
                <input id="image" type="file" name="image" onchange="$('#upload-file-info').html(this.files[0].name);" hidden>
                Прикрепить картинку&hellip;
            </label>
            <span class="badge badge-info" id="upload-file-info"></span>
            {!! textError('image') !!}
            <br>

            <div class="custom-control custom-checkbox">
                <input type="hidden" value="0" name="closed">
                <input type="checkbox" class="custom-control-input" value="1" name="closed" id="closed"{{ getInput('closed', $news->closed) ? ' checked' : '' }}>
                <label class="custom-control-label" for="closed">Закрыть комментарии</label>
            </div>

            <div class="custom-control custom-checkbox">
                <input type="hidden" value="0" name="top">
                <input type="checkbox" class="custom-control-input" value="1" name="top" id="top"{{ getInput('top', $news->top) ? ' checked' : '' }}>
                <label class="custom-control-label" for="top">Показывать на главной</label>
            </div>

            <button class="btn btn-primary">Изменить</button>
        </form>
    </div>
@stop
