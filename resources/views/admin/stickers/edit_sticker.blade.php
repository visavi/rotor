@extends('layout')

@section('title')
    Редактирование стикера
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('main.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/stickers">{{ trans('stickers.title') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/stickers/{{ $sticker->category->id }}">{{ $sticker->category->name }}</a></li>
            <li class="breadcrumb-item active">Редактирование стикера</li>
        </ol>
    </nav>
@stop

@section('content')
    <img src="{{ $sticker->name }}" alt=""><br>

    <div class="form">
        <form action="/admin/stickers/sticker/edit/{{ $sticker->id }}?page={{ $page }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('category') }}">
                <label for="inputCategory">Категория</label>

                <?php $inputCategory = getInput('cid', $sticker->category->id); ?>
                <select class="form-control" id="inputCategory" name="cid">
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}"{{ ($inputCategory === $category->id) ? ' selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
                {!! textError('category') !!}
            </div>

            <div class="form-group{{ hasError('code') }}">
                <label for="code">Код стикера:</label>
                <input type="text" class="form-control" id="code" name="code" maxlength="20" value="{{ getInput('code', $sticker->code) }}" required>
                {!! textError('code') !!}
            </div>

            <p class="text-muted font-italic">
                Код стикера должен начинаться со знака двоеточия
            </p>
            <button class="btn btn-primary">Изменить</button>
        </form>
    </div>
@stop
