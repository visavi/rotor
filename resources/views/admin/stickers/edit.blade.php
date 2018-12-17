@extends('layout')

@section('title')
    Редактирование стикера
@stop

@section('content')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">Панель</a></li>
            <li class="breadcrumb-item"><a href="/admin/stickers">Стикеры</a></li>
            <li class="breadcrumb-item active">Редактирование стикера</li>
        </ol>
    </nav>

    <h1>Редактирование стикера</h1>

    <img src="{{ $sticker->name }}" alt=""></b><br>

    <div class="form">
        <form action="/admin/stickers/edit/{{ $sticker->id }}?page={{ $page }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('category') }}">
                <label for="inputCategory">Категория</label>

                <?php $inputCategory = getInput('cid', $sticker->category->id); ?>
                <select class="form-control" id="inputCategory" name="cid">
                    <option value="0"{{ empty($inputCategory) ? ' selected' : '' }}>Общие стикеры</option>

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
