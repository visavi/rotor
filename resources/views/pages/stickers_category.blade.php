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
    <div class="container section mb-3 shadow">
        @if ($stickers->isNotEmpty())
            <div class="row">
                @foreach ($stickers as $sticker)
                    <div class="col-md-3 col-sm-6">
                        <img src="{{ $sticker['name'] }}" alt="{{ $sticker['code'] }}"><br>
                        <b>{{ $sticker['code'] }}</b>
                    </div>
                @endforeach
            </div>

            {{ __('stickers.total_stickers') }}: <b>{{ $stickers->total() }}</b><br>
        @else
            {!! showError(__('stickers.empty_stickers')) !!}
        @endif
    </div>

    {{ $stickers->links() }}
@stop
