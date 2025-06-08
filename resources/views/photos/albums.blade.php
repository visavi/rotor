@extends('layout')

@section('title', __('photos.albums') . ' (' . __('main.page_num', ['page' => $albums->currentPage()]) . ')')

@section('header')
    <h1>{{ __('photos.albums') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('photos.index') }}">{{ __('index.photos') }}</a></li>
            <li class="breadcrumb-item active">{{ __('photos.albums') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($albums->isNotEmpty())
        @foreach ($albums as $data)
            <div class="section mb-3 shadow">
                <div class="section-title">
                    <i class="fa fa-image"></i>
                    <a class="" href="{{ route('photos.user-albums', ['user' => $data->user->login]) }}">{{ $data->user->getName() }}</a>
                </div>

                {{ $data->cnt }} {{ __('photos.photos') }} / {{ $data->count_comments }} {{ __('main.comments') }}
            </div>
        @endforeach

        <div class="mb-3">
            {{ __('photos.total_albums') }}: <b>{{ $albums->total() }}</b>
        </div>
    @else
        {{ showError(__('photos.empty_albums')) }}
    @endif

    {{ $albums->links() }}
@stop
