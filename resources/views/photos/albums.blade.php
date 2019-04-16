@extends('layout')

@section('title')
    {{ trans('photos.albums') }} ({{ trans('main.page_num', ['page' => $page->current]) }})
@stop

@section('header')
    <h1>{{ trans('photos.albums') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/photos">{{ trans('photos.title') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('photos.albums') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($albums->isNotEmpty())
        @foreach ($albums as $data)

            <i class="fa fa-image"></i>
            <b><a href="/photos/albums/{{ $data->login }}">{{ $data->login }}</a></b> ({{ $data->cnt }} {{ trans('photos.photos') }} / {{ $data->count_comments }} {{ trans('main.comments') }})<br>

        @endforeach

        {!! pagination($page) !!}

        {{ trans('photos.total_albums') }}: <b>{{ $page->total }}</b><br><br>
    @else
        {!! showError(trans('photos.empty_albums')) !!}
    @endif
@stop
