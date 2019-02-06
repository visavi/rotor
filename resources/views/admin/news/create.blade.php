@extends('layout')

@section('title')
    Создание новости
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">Панель</a></li>
            <li class="breadcrumb-item"><a href="/admin/news">Новости</a></li>
            <li class="breadcrumb-item active">Создание новости</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="form cut">
        <form action="/admin/news/create" method="post" enctype="multipart/form-data">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('title') }}">
                <label for="title">Название:</label>
                <input type="text" class="form-control" id="title" name="title" maxlength="100" value="{{ getInput('title') }}" required>
                {!! textError('title') !!}
            </div>

            <div class="form-group{{ hasError('text') }}">
                <label for="text">Текст:</label>
                <textarea class="form-control markItUp" maxlength="10000" id="text" rows="10" name="text" required>{{ getInput('text') }}</textarea>
                <span class="js-textarea-counter"></span>
                {!! textError('text') !!}
            </div>

            <label class="btn btn-sm btn-secondary" for="image">
                <input id="image" type="file" name="image" onchange="$('#upload-file-info').html(this.files[0].name);" hidden>
                Прикрепить картинку&hellip;
            </label>
            <span class="badge badge-info" id="upload-file-info"></span>
            {!! textError('image') !!}
            <br>

            <div class="custom-control custom-checkbox">
                <input type="hidden" value="0" name="closed">
                <input type="checkbox" class="custom-control-input" value="1" name="closed" id="closed"{{ getInput('closed') ? ' checked' : '' }}>
                <label class="custom-control-label" for="closed">Закрыть комментарии</label>
            </div>

            <div class="custom-control custom-checkbox">
                <input type="hidden" value="0" name="top">
                <input type="checkbox" class="custom-control-input" value="1" name="top" id="top"{{ getInput('top') ? ' checked' : '' }}>
                <label class="custom-control-label" for="top">Показывать на главной</label>
            </div>

            <button class="btn btn-primary">Создать</button>
        </form>
    </div>
@stop
