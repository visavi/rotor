@extends('layout')

@section('title')
    {{ __('photos.albums') }} ({{ __('main.page_num', ['page' => $page->current]) }})
@stop

@section('header')
    <h1>{{ __('photos.albums') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/photos">{{ __('index.photos') }}</a></li>
            <li class="breadcrumb-item active">{{ __('photos.albums') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($albums->isNotEmpty())
        @foreach ($albums as $data)

            <i class="fa fa-image"></i>
            <b><a href="/photos/albums/{{ $data->login }}">{{ $data->login }}</a></b> ({{ $data->cnt }} {{ __('photos.photos') }} / {{ $data->count_comments }} {{ __('main.comments') }})<br>

        @endforeach

        {!! pagination($page) !!}

        {{ __('photos.total_albums') }}: <b>{{ $page->total }}</b><br><br>
    @else
        {!! showError(__('photos.empty_albums')) !!}
    @endif
@stop
