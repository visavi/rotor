@extends('layout')

@section('title')
    Публикация нового файла
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/loads">Загрузки</a></li>
            <li class="breadcrumb-item active">Публикация</li>
        </ol>
    </nav>
@stop

@section('content')
    <form action="/downs/create" method="post" enctype="multipart/form-data">
        <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

        <div class="form-group{{ hasError('category') }}">
            <label for="inputCategory">Категория</label>

            <select class="form-control" id="inputCategory" name="cid">
                @foreach ($loads as $data)

                    <option value="{{ $data->id }}"{{ ($cid === $data->id && ! $data->closed) ? ' selected' : '' }}{{ $data->closed ? ' disabled' : '' }}>{{ $data->name }}</option>

                    @if ($data->children->isNotEmpty())
                        @foreach($data->children as $datasub)
                            <option value="{{ $datasub->id }}"{{ $cid === $datasub->id && ! $datasub->closed ? ' selected' : '' }}{{ $datasub->closed ? ' disabled' : '' }}>– {{ $datasub->name }}</option>
                        @endforeach
                    @endif
                @endforeach

            </select>
            {!! textError('category') !!}
        </div>

        <div class="form-group{{ hasError('title') }}">
            <label for="inputTitle">Название:</label>
            <input class="form-control" id="inputTitle" name="title" value="{{ getInput('title') }}" required>
            {!! textError('title') !!}
        </div>

        <div class="form-group{{ hasError('text') }}">
            <label for="text">Текст:</label>
            <textarea class="form-control markItUp" id="text" rows="10" name="text" required>{{ getInput('text') }}</textarea>
            {!! textError('text') !!}
        </div>

        <label class="btn btn-sm btn-secondary" for="files">
            <input type="file" id="files" name="files[]" onchange="$('#upload-file-info').html((this.files.length > 1) ? this.files.length + ' файлов' : this.files[0].name);" hidden multiple>
            Прикрепить файлы&hellip;
        </label>
        <span class="badge badge-info" id="upload-file-info"></span>
        {!! textError('files') !!}
        <br>

        <p class="text-muted font-italic">
            Можно загрузить до {{ setting('maxfiles') }} файлов<br>
            Максимальный вес файла: {{ formatSize(setting('fileupload')) }}<br>
            Допустимые расширения файлов: {{ str_replace(',', ', ', setting('allowextload')) }}<br>
            Допустимые размеры картинок: от 100px
        </p>

        <button class="btn btn-primary">Загрузить</button>
    </form>
@stop
