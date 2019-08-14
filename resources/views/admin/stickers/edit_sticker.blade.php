@extends('layout')

@section('title')
    {{ trans('stickers.edit_sticker') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/stickers">{{ trans('index.stickers') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/stickers/{{ $sticker->category->id }}">{{ $sticker->category->name }}</a></li>
            <li class="breadcrumb-item active">{{ trans('stickers.edit_sticker') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <img src="{{ $sticker->name }}" alt=""><br>

    <div class="form">
        <form action="/admin/stickers/sticker/edit/{{ $sticker->id }}?page={{ $page }}" method="post">
            @csrf
            <div class="form-group{{ hasError('category') }}">
                <label for="inputCategory">{{ trans('stickers.category') }}</label>

                <?php $inputCategory = getInput('cid', $sticker->category->id); ?>
                <select class="form-control" id="inputCategory" name="cid">
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}"{{ ($inputCategory === $category->id) ? ' selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
                <div class="invalid-feedback">{{ textError('category') }}</div>
            </div>

            <div class="form-group{{ hasError('code') }}">
                <label for="code">{{ trans('stickers.sticker_code') }}:</label>
                <input type="text" class="form-control" id="code" name="code" maxlength="20" value="{{ getInput('code', $sticker->code) }}" required>
                <div class="invalid-feedback">{{ textError('code') }}</div>
            </div>

            <p class="text-muted font-italic">
                {{ trans('stickers.valid_sticker_code') }}
            </p>
            <button class="btn btn-primary">{{ trans('stickers.change') }}</button>
        </form>
    </div>
@stop
