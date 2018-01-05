@extends('layout')

@section('title')
    Добавление смайла
@stop

@section('content')

    <h1>Добавление смайла</h1>

    <div class="form">
        <form action="/admin/smiles/create" method="post" enctype="multipart/form-data">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('code') }}">
                <label for="code">Код смайла:</label>
                <input type="text" class="form-control" id="code" name="code" maxlength="20" value="{{ getInput('code') }}" required>
                {!! textError('code') !!}
            </div>

            <label class="btn btn-sm btn-secondary" for="smile">
                <input id="smile" type="file" name="smile" onchange="$('#upload-file-info').html(this.files[0].name);" hidden>
                Прикрепить смайл&hellip;
            </label>
            <span class="badge badge-info" id="upload-file-info"></span>
            {!! textError('smile') !!}
            <br>

            <button class="btn btn-primary">Загрузить</button>
        </form>
    </div><br>

    <p class="text-muted font-italic">
        Код смайла должен начинаться со знака двоеточия<br>
        Разрешается добавлять смайлы с расширением jpg, jpeg, gif, png<br>
        Весом не более {{ formatSize(setting('smilemaxsize')) }} и размером до {{ setting('smilemaxweight') }} px<br><br>
    </p>

    <i class="fa fa-arrow-circle-left"></i> <a href="/admin/smiles">Вернуться</a><br>
    <i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>
@stop
