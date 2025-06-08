@extends('layout')

@section('title', __('index.stickers'))

@section('header')
    <div class="float-end">
        <a class="btn btn-success" href="/admin/stickers/sticker/create">{{ __('main.upload') }}</a>
    </div>

    <h1>{{ __('index.stickers') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.stickers') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($categories->isNotEmpty())
        @foreach ($categories as $category)
            <div class="section mb-3 shadow">
                <div class="section-title">
                    <i class="far fa-smile"></i>  <b><a href="/admin/stickers/{{ $category->id }}">{{ $category->name }}</a></b> ({{ $category->cnt }})

                    <div class="float-end">
                        <a href="/admin/stickers/edit/{{ $category->id }}" data-bs-toggle="tooltip" title="{{ __('main.edit') }}"><i class="fa fa-pencil-alt"></i></a>
                        <a href="/admin/stickers/delete/{{ $category->id }}?_token={{ csrf_token() }}" data-bs-toggle="tooltip" title="{{ __('main.delete') }}" onclick="return confirm('{{ __('stickers.confirm_delete_category') }}')"><i class="fa fa-times"></i></a>
                    </div>
                </div>
            </div>
        @endforeach
    @else
        {{ showError(__('stickers.empty_categories')) }}
    @endif

    <div class="section-form mb-3 shadow">
        <form action="/admin/stickers/create" method="post">
            @csrf
            <div class="input-group{{ hasError('name') }}">
                <input type="text" class="form-control" id="name" name="name" maxlength="50" value="{{ getInput('name') }}" placeholder="{{ __('stickers.category') }}" required>
                <button class="btn btn-primary">{{ __('main.create') }}</button>
            </div>
            <div class="invalid-feedback">{{ textError('name') }}</div>
        </form>
    </div>
@stop
