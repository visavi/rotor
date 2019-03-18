@extends('layout')

@section('title')
    {{ trans('stickers.create_sticker') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('main.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/stickers">{{ trans('stickers.title') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('stickers.create_sticker') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="form">
        <form action="/admin/stickers/sticker/create" method="post" enctype="multipart/form-data">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('category') }}">
                <label for="inputCategory">Категория</label>

                <select class="form-control" id="inputCategory" name="cid">
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}"{{ ($cid === $category->id) ? ' selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
                {!! textError('category') !!}
            </div>

            <div class="form-group{{ hasError('code') }}">
                <label for="code">Код стикера:</label>
                <input type="text" class="form-control" id="code" name="code" maxlength="20" value="{{ getInput('code') }}" required>
                {!! textError('code') !!}
            </div>

            <label class="btn btn-sm btn-secondary" for="sticker">
                <input id="sticker" type="file" name="sticker" onchange="$('#upload-file-info').html(this.files[0].name);" hidden>
                Прикрепить стикер&hellip;
            </label>
            <span class="badge badge-info" id="upload-file-info"></span>
            {!! textError('sticker') !!}
            <br>
            <button class="btn btn-primary">Загрузить</button>
        </form>
    </div><br>

    <p class="text-muted font-italic">
        Код стикера должен начинаться со знака двоеточия<br>
        Разрешается добавлять стикеры с расширением jpg, jpeg, gif, png<br>
        Весом не более {{ formatSize(setting('stickermaxsize')) }} и размером до {{ setting('stickermaxweight') }} px<br><br>
    </p>
@stop
