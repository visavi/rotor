@extends('layout')

@section('title', __('stickers.edit_sticker'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('index.panel') }}</a></li>
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
            <div class="mb-3{{ hasError('category') }}">
                <label for="inputCategory" class="form-label">{{ __('stickers.category') }}</label>

                <?php $inputCategory = (int) getInput('cid', $sticker->category->id); ?>
                <select class="form-select" id="inputCategory" name="cid">
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}"{{ ($inputCategory === $category->id) ? ' selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
                <div class="invalid-feedback">{{ textError('category') }}</div>
            </div>

            <div class="mb-3{{ hasError('code') }}">
                <label for="code" class="form-label">{{ __('stickers.sticker_code') }}:</label>
                <input type="text" class="form-control" id="code" name="code" maxlength="20" value="{{ getInput('code', $sticker->code) }}" required>
                <div class="invalid-feedback">{{ textError('code') }}</div>
            </div>

            <p class="text-muted fst-italic">
                {{ __('stickers.valid_sticker_code') }}
            </p>
            <button class="btn btn-primary">{{ __('main.change') }}</button>
        </form>
    </div>
@stop
