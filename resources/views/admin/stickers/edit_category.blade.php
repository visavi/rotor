@extends('layout')

@section('title')
    {{ __('stickers.edit_category') }} {{ $category->name }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/stickers">{{ __('index.stickers') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/stickers/{{ $category->id }}">{{ $category->name }}</a></li>
            <li class="breadcrumb-item active">{{ __('stickers.edit_category') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="form mb-3">
        <form action="/admin/stickers/edit/{{ $category->id }}" method="post">
            @csrf
            <div class="form-group{{ hasError('name') }}">
                <label for="name">{{ __('stickers.category') }}:</label>
                <input class="form-control" name="name" id="name" maxlength="50" value="{{ getInput('name', $category->name) }}" required>
                <div class="invalid-feedback">{{ textError('name') }}</div>
            </div>
            <button class="btn btn-primary">{{ __('main.change') }}</button>
        </form>
    </div>
@stop
