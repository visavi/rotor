@extends('layout')

@section('title')
    Редактирование загрузки {{ $down->title }}
@stop

@section('content')

    <h1>Редактирование загрузки {{ $down->title }}</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">Панель</a></li>
            <li class="breadcrumb-item"><a href="/admin/load">Загрузки</a></li>

            @if ($down->category->parent->id)
                <li class="breadcrumb-item"><a href="/admin/load/{{ $down->category->parent->id }}">{{ $down->category->parent->name }}</a></li>
            @endif

            <li class="breadcrumb-item"><a href="/admin/load/{{ $down->category->id }}">{{ $down->category->name }}</a></li>
            <li class="breadcrumb-item active">Редактирование</li>
            <li class="breadcrumb-item"><a href="/down/{{ $down->id }}">Обзор загрузки</a></li>
        </ol>
    </nav>

    <div class="form mb-3">
        <form action="/admin/down/edit/{{ $down->id }}" method="post" enctype="multipart/form-data">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            @if ($down->link)
                <i class="fa fa-download"></i>
                <b><a href="/uploads/files/{{ $down->link }}">{{ $down->link }}</a></b> ({{ formatFileSize(UPLOADS . '/files/' . $down->link) }}) (<a href="/admin/load?act=delfile" onclick="return confirm('Вы действительно хотите удалить данный файл?')">Удалить</a>)<br>
            @else
                Прикрепить файл ({{ setting('allowextload') }}):<br>
                <label class="btn btn-sm btn-secondary" for="file">
                    <input id="file" type="file" name="file" onchange="$('#upload-file-info').html(this.files[0].name);" hidden>
                    Прикрепить файл&hellip;
                </label>
                <span class="badge badge-info" id="upload-file-info"></span>
                {!! textError('file') !!}
                <br>
            @endif

            @if (! in_array($down->ext, ['jpg', 'jpeg', 'gif', 'png']))

                @if ($down->screen)
                    <i class="fa fa-image"></i> <b><a href="/uploads/screen/{{ $down->screen }}">{{ $down->screen }}</a></b> ({{ formatFileSize(UPLOADS.'/screen/' . $down->screen ) }}) (<a href="/admin/load?act=delscreen" onclick="return confirm('Вы действительно хотите удалить данный скриншот?')">Удалить</a>)<br><br>
                    {!! resizeImage('uploads/screen/', $down->screen) !!}<br>
                @else
                    Прикрепить скрин (jpg,jpeg,gif,png)<br>
                    <label class="btn btn-sm btn-secondary" for="screen">
                        <input id="screen" type="file" name="screen" onchange="$('#upload-screen-info').html(this.files[0].name);" hidden>
                        Прикрепить картинку&hellip;
                    </label>
                    <span class="badge badge-info" id="upload-screen-info"></span>
                    {!! textError('screen') !!}
                    <br>
                @endif
            @endif

            <div class="form-group{{ hasError('title') }}">
                <label for="title">Название:</label>
                <input class="form-control" name="title" id="title" maxlength="50" value="{{ getInput('title', $down->title) }}" required>
                {!! textError('title') !!}
            </div>

            <div class="form-group{{ hasError('text') }}">
                <label for="text">Описание:</label>
                <textarea class="form-control markItUp" id="text" name="text" rows="5">{{ getInput('note', $down->text) }}</textarea>
                {!! textError('text') !!}
            </div>

            <button class="btn btn-primary">Сохранить</button>
        </form>
    </div>
@stop
