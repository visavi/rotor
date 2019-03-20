@extends('layout')

@section('title')
    {{ trans('stickers.edit_category') }} {{ $category->name }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('main.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/stickers">{{ trans('stickers.title') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/stickers/{{ $category->id }}">{{ $category->name }}</a></li>
            <li class="breadcrumb-item active">{{ trans('stickers.edit_category') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="form mb-3">
        <form action="/admin/stickers/edit/{{ $category->id }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('name') }}">
                <label for="name">{{ trans('stickers.category') }}:</label>
                <input class="form-control" name="name" id="name" maxlength="50" value="{{ getInput('name', $category->name) }}" required>
                {!! textError('name') !!}
            </div>
            <button class="btn btn-primary">{{ trans('main.change') }}</button>
        </form>
    </div>
@stop
