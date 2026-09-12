@extends('layout')

@section('title', $category->name)

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/stickers">{{ __('index.stickers') }}</a></li>
            <li class="breadcrumb-item active">{{ $category->name }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($stickers->isNotEmpty())
        <div class="section mb-3 shadow">
            <div class="section-body">
                <div class="sticker-grid">
                    @foreach ($stickers as $sticker)
                        <span class="sticker-item">
                            <img src="{{ $sticker->name }}" alt="{{ $sticker->code }}" loading="lazy">
                            <b>{{ $sticker->code }}</b>
                        </span>
                    @endforeach
                </div>

                {{ $stickers->links() }}

                <div class="text-muted">
                    {{ __('stickers.total_stickers') }}: <b>{{ $stickers->total() }}</b>
                </div>
            </div>
        </div>
    @else
        {{ showError(__('stickers.empty_stickers')) }}
    @endif
@stop
