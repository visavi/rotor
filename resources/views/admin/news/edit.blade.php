@extends('layout')

@section('title')
    Редактирование новости
@stop

@section('content')

    <h1>Редактирование новости</h1>

    <div class="form cut">
        <form action="/admin/news/edit/{{ $news->id }}?page={{ $page }}" method="post" enctype="multipart/form-data">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('title') }}">
                <label for="title">Заголовок:</label>
                <input type="text" class="form-control" id="title" name="title" maxlength="100" value="{{ getInput('title', $news->title) }}" required>
                {!! textError('title') !!}
            </div>

            <div class="form-group{{ hasError('text') }}">
                <label for="markItUp">Текст:</label>
                <textarea class="form-control" id="markItUp" rows="10" name="text" required>{{ getInput('text', $news->text) }}</textarea>
                {!! textError('text') !!}
            </div>

            @if ($news->image && file_exists(UPLOADS.'/news/'.$news->image))

                <a href="/uploads/news/{{ $news->image }}">{!! resizeImage('uploads/news/', $news->image, ['size' => 100, 'alt' => $news['title']]) !!}</a><br>
                <b>{{ $news->image }}</b> ({{ formatFileSize(UPLOADS.'/news/'.$news->image) }})<br><br>
            @endif

            <label class="btn btn-sm btn-secondary" for="image">
                <input id="image" type="file" name="image" onchange="$('#upload-file-info').html(this.files[0].name);" hidden>
                Прикрепить картинку&hellip;
            </label>
            <span class="badge badge-info" id="upload-file-info"></span>
            {!! textError('image') !!}
            <br>

            <div class="form-check">
                <label class="form-check-label">
                    <input type="hidden" value="0" name="closed">
                    <input name="closed" class="form-check-input" type="checkbox" value="1"{{ getInput('closed', $news->closed) ? ' checked' : '' }}>
                    Закрыть комментарии
                </label>
            </div>

            <div class="form-check">
                <label class="form-check-label">
                    <input type="hidden" value="0" name="top">
                    <input name="top" class="form-check-input" type="checkbox" value="1"{{ getInput('top', $news->top) ? ' checked' : '' }}>
                    Показывать на главной
                </label>
            </div>

            <button class="btn btn-primary">Изменить</button>
        </form>
    </div><br>

    <i class="fa fa-arrow-circle-left"></i> <a href="/admin/news?page={{ $page }}">Вернуться</a><br>
    <i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>
@stop
