@extends('layout')

@section('title')
    Добавление фотографии
@stop

@section('content')

    <h1>Добавление фотографии</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/gallery">Галерея</a></li>
            <li class="breadcrumb-item active">Добавление фотографии</li>
        </ol>
    </nav>

    <div class="form">
        <form action="/gallery/create" method="post" enctype="multipart/form-data">
            <input type="hidden" name="token" value="{{  $_SESSION['token'] }}">

            <div class="form-group{{ hasError('title') }}">
                <label for="inputTitle">Название:</label>
                <input type="text" class="form-control" id="inputTitle" name="title" maxlength="50" value="{{ getInput('title') }}" required>
                {!! textError('title') !!}
            </div>

            <div class="form-group{{ hasError('text') }}">
                <label for="text">Подпись к фото:</label>
                <textarea class="form-control markItUp" id="text" rows="5" name="text">{{ getInput('text') }}</textarea>
                {!! textError('text') !!}
            </div>

            <label class="btn btn-sm btn-secondary" for="photo">
                <input type="file" id="photo" name="photo" onchange="$('#upload-file-info').html(this.files[0].name);" hidden>
                Прикрепить фото&hellip;
            </label>
            <span class="badge badge-info" id="upload-file-info"></span>
            {!! textError('photo') !!}
            <br>

            <div class="custom-control custom-checkbox">
                <input type="hidden" value="0" name="closed">
                <input type="checkbox" class="custom-control-input" value="1" name="closed" id="closed"{{ getInput('closed') ? ' checked' : '' }}>
                <label class="custom-control-label" for="closed">Закрыть комментарии</label>
            </div>

            <button class="btn btn-success">Добавить</button>
        </form>
    </div><br>

    Разрешается добавлять фотки с расширением jpg, jpeg, gif и png<br>
    Весом не более {{ formatSize(setting('filesize')) }} и размером от 100 до {{ setting('fileupfoto') }} px<br><br>
@stop
