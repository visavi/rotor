@extends('layout')

@section('title')
    {{ trans('index.stickers') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ trans('index.stickers') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($categories->isNotEmpty())
        @foreach($categories as $category)
            <div class="bg-light p-2 mb-1 border">
                <i class="far fa-smile"></i>  <b><a href="/stickers/{{ $category->id }}">{{ $category->name }}</a></b> ({{ $category->cnt }})
            </div>
        @endforeach
    @else
        {!! showError(trans('stickers.empty_categories')) !!}
    @endif
@stop
