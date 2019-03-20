@extends('layout')

@section('title')
    {{ trans('stickers.title') }}
@stop

@section('header')
    <div class="float-right">
        <a class="btn btn-success" href="/admin/stickers/sticker/create">{{ trans('main.upload') }}</a>
    </div><br>

    <h1>{{ trans('stickers.title') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('main.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('stickers.title') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($categories->isNotEmpty())
        @foreach($categories as $category)
            <div class="bg-light p-2 mb-1 border">
                <div class="float-right">
                    <a href="/admin/stickers/edit/{{ $category->id }}" data-toggle="tooltip" title="{{ trans('main.edit') }}"><i class="fa fa-pencil-alt"></i></a>

                    <a href="/admin/stickers/delete/{{ $category->id }}?token={{ $_SESSION['token'] }}" data-toggle="tooltip" title="{{ trans('main.delete') }}" onclick="return confirm('{{ trans('stickers.confirm_delete_category') }}')"><i class="fa fa-times"></i></a>
                </div>

                <i class="far fa-smile"></i>  <b><a href="/admin/stickers/{{ $category->id }}">{{ $category->name }}</a></b> ({{ $category->cnt }})
            </div>
        @endforeach
    @else
        {!! showError(trans('stickers.empty_categories')) !!}
    @endif

    <div class="form my-3">
        <form action="/admin/stickers/create" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">
            <div class="form-inline">
                <div class="form-group{{ hasError('name') }}">
                    <input type="text" class="form-control" id="name" name="name" maxlength="50" value="{{ getInput('name') }}" placeholder="{{ trans('stickers.category') }}" required>
                </div>

                <button class="btn btn-primary">{{ trans('main.create') }}</button>
            </div>
            {!! textError('name') !!}
        </form>
    </div>
@stop
