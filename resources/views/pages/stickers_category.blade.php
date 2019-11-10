@extends('layout')

@section('title')
    {{ $category->name }}
@stop

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
        @foreach ($stickers as $sticker)
            <div class="bg-light p-2 mb-1 border">
                <img src="{{ $sticker['name'] }}" alt=""><br>
                <b>{{ $sticker['code'] }}</b>
            </div>
        @endforeach

        {{ __('stickers.total_stickers') }}: <b>{{ $stickers->total() }}</b><br>
    @else
        {!! showError(__('stickers.empty_stickers')) !!}
    @endif

    {{ $stickers->links('app/_paginator') }}
@stop
