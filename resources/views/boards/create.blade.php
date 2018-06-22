@extends('layout')

@section('title')
    Добавление объявления
@stop

@section('content')

    <h1>Добавление объявления</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/boards">Объявления</a></li>
            <li class="breadcrumb-item active">Добавление объявления</li>
        </ol>
    </nav>

    <form action="/items/create" method="post" enctype="multipart/form-data">
        <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

        <div class="form-group{{ hasError('category') }}">
            <label for="inputCategory">Категория</label>

            <select class="form-control" id="inputCategory" name="bid">
                @foreach ($boards as $board)

                    <option value="{{ $board->id }}"{{ ($bid == $board->id && ! $board->closed) ? ' selected' : '' }}{{ $board->closed ? ' disabled' : '' }}>{{ $board->name }}</option>

                    @if ($board->children->isNotEmpty())
                        @foreach($board->children as $boardsub)
                            <option value="{{ $boardsub->id }}"{{ $bid == $boardsub->id && ! $boardsub->closed ? ' selected' : '' }}{{ $boardsub->closed ? ' disabled' : '' }}>– {{ $boardsub->name }}</option>
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
            <label for="text">Описание:</label>
            <textarea class="form-control markItUp" id="text" rows="10" name="text" required>{{ getInput('text') }}</textarea>
            {!! textError('text') !!}
        </div>

        <div class="form-group{{ hasError('price') }}">
            <label for="inputPrice">Цена ₽:</label>
            <input class="form-control" id="inputPrice" name="price" value="{{ getInput('price') }}" required>
            {!! textError('price') !!}
        </div>

        <label class="btn btn-sm btn-secondary" for="files">
            <input type="file" id="files" name="files[]" onchange="$('#upload-file-info').html((this.files.length > 1) ? this.files.length + ' файлов' : this.files[0].name);" hidden multiple>
            Прикрепить фото&hellip;
        </label>
        <span class="badge badge-info" id="upload-file-info"></span>
        {!! textError('files') !!}
        <br>

        <p class="text-muted font-italic">
            Можно загрузить до {{ setting('maxfiles') }} фото<br>
            Максимальный вес фото: {{ formatSize(setting('filesize')) }} и размером от 100px<br>
            Допустимые расширения фото: jpg, jpeg, gif и png
        </p>

        <button class="btn btn-primary">Загрузить</button>
    </form>
@stop
