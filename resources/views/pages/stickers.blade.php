@extends('layout')

@section('title', __('index.stickers'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ __('index.stickers') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($categories->isNotEmpty())
        @foreach ($categories as $category)
            <div class="section mb-3 shadow">
                <div class="section-title">
                    <i class="far fa-smile"></i> <a href="/stickers/{{ $category->id }}">{{ $category->name }}</a> ({{ $category->cnt }})
                </div>
            </div>
        @endforeach
    @else
        {!! showError(__('stickers.empty_categories')) !!}
    @endif
@stop
