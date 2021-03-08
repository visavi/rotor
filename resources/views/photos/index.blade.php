@extends('layout')

@section('title', __('index.photos') . ' (' . __('main.page_num', ['page' => $photos->currentPage()]) .')')

@section('header')
    <div class="float-right">
        @if (getUser())
            <a class="btn btn-success" href="/photos/create">{{ __('main.add') }}</a>

            @if (isAdmin())
                <a class="btn btn-light" href="/admin/photos?page={{ $photos->currentPage() }}"><i class="fas fa-wrench"></i></a>
            @endif
        @endif
    </div>

    <h1>{{ __('index.photos') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ __('index.photos') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if (getUser())
        {{ __('main.my') }}:
        <a href="/photos/albums/{{ getUser('login') }}">{{ __('photos.photos') }}</a>,
        <a href="/photos/comments/active/{{ getUser('login') }}">{{ __('main.comments') }}</a> /
    @endif

    {{ __('main.all') }}:
    <a href="/photos/albums">{{ __('photos.albums') }}</a>,
    <a href="/photos/comments">{{ __('main.comments') }}</a> /
    <a href="/photos/top">{{ __('photos.top_photos') }}</a>
    <hr>

    @if ($photos->isNotEmpty())
        @foreach ($photos as $photo)
            <div class="section mb-3 shadow">
                <div class="section-header d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="section-title">
                            <i class="fa fa-image"></i>
                            <a href="/photos/{{ $photo->id }}">{{ $photo->title }}</a>
                        </div>
                    </div>

                    <div class="text-right js-rating">
                        @if (getUser() && getUser('id') !== $photo->user_id)
                            <a class="post-rating-down<?= $photo->vote === '-' ? ' active' : '' ?>" href="#" onclick="return changeRating(this);" data-id="{{ $photo->id }}" data-type="{{ $photo->getMorphClass() }}" data-vote="-" data-token="{{ $_SESSION['token'] }}"><i class="fa fa-thumbs-down"></i></a>
                        @endif
                        <b>{{ formatNum($photo->rating) }}</b>
                        @if (getUser() && getUser('id') !== $photo->user_id)
                            <a class="post-rating-up<?= $photo->vote === '+' ? ' active' : '' ?>" href="#" onclick="return changeRating(this);" data-id="{{ $photo->id }}" data-type="{{ $photo->getMorphClass() }}" data-vote="+" data-token="{{ $_SESSION['token'] }}"><i class="fa fa-thumbs-up"></i></a>
                        @endif
                    </div>
                </div>

                <div class="section-content">
                    @include('app/_carousel', ['model' => $photo, 'path' => '/photos'])

                    @if ($photo->text)
                        {{ bbCode($photo->text) }}<br>
                    @endif

                    {{ __('main.added') }}: {{ $photo->user->getProfile() }} ({{ dateFixed($photo->created_at) }})<br>
                    <a href="/photos/comments/{{ $photo->id }}">{{ __('main.comments') }}</a> ({{ $photo->count_comments }})
                    <a href="/photos/end/{{ $photo->id }}">&raquo;</a>
                </div>
            </div>
        @endforeach

        {{ $photos->links() }}

        {{ __('photos.total_photos') }}: <b>{{ $photos->total() }}</b><br>
    @else
        {{ showError(__('photos.empty_photos')) }}
    @endif
@stop
