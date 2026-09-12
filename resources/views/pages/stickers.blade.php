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
        <div class="sticker-categories">
            @foreach ($categories as $category)
                <a class="sticker-category" href="/stickers/{{ $category->id }}">
                    <span class="sticker-category-title">
                        {{ $category->name }} <span class="badge bg-adaptive">{{ $category->cnt }}</span>
                    </span>

                    <span class="sticker-category-preview">
                        @foreach ($previews[$category->id] ?? [] as $sticker)
                            <img src="{{ $sticker->name }}" alt="" loading="lazy">
                        @endforeach
                    </span>
                </a>
            @endforeach
        </div>
    @else
        {{ showError(__('stickers.empty_categories')) }}
    @endif
@stop
