@extends('layout')

@section('title')
    Добавление смайла
@stop

@section('content')

    <h1>Добавление смайла</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">Панель</a></li>
            <li class="breadcrumb-item"><a href="/admin/smiles">Смайлы</a></li>
            <li class="breadcrumb-item active">Добавление смайла</li>
        </ol>
    </nav>

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
@stop
