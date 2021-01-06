@extends('layout')

@section('title', __('stickers.edit_sticker'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/stickers">{{ __('index.stickers') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/stickers/{{ $sticker->category->id }}">{{ $sticker->category->name }}</a></li>
            <li class="breadcrumb-item active">{{ __('stickers.edit_sticker') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <img src="{{ $sticker->name }}" alt=""><br>

    <div class="section-form mb-3 shadow">
        <form action="/admin/stickers/sticker/edit/{{ $sticker->id }}?page={{ $page }}" method="post">
            @csrf
            <div class="form-group{{ hasError('category') }}">
                <label for="inputCategory">{{ __('stickers.category') }}</label>

                <?php $inputCategory = (int) getInput('cid', $sticker->category->id); ?>
                <select class="form-control" id="inputCategory" name="cid">
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}"{{ ($inputCategory === $category->id) ? ' selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
                <div class="invalid-feedback">{{ textError('category') }}</div>
            </div>

            <div class="form-group{{ hasError('code') }}">
                <label for="code">{{ __('stickers.sticker_code') }}:</label>
                <input type="text" class="form-control" id="code" name="code" maxlength="20" value="{{ getInput('code', $sticker->code) }}" required>
                <div class="invalid-feedback">{{ textError('code') }}</div>
            </div>

            <p class="text-muted font-italic">
                {{ __('stickers.valid_sticker_code') }}
            </p>
            <button class="btn btn-primary">{{ __('stickers.change') }}</button>
        </form>
    </div>
@stop
