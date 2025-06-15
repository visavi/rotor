@extends('layout')

@section('title', __('index.photos') . ' (' . __('main.page_num', ['page' => $photos->currentPage()]) .')')

@section('header')
    <div class="float-end">
        @if (isAdmin() || (getUser() && setting('photos_create')))
            <a class="btn btn-success" href="{{ route('photos.create') }}">{{ __('main.add') }}</a>

            @if (isAdmin())
                <a class="btn btn-light" href="{{ route('admin.photos.index', ['page' => $photos->currentPage()]) }}"><i class="fas fa-wrench"></i></a>
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
        <a href="{{ route('photos.user-albums') }}" class="badge bg-adaptive">{{ __('photos.photos') }}</a>
        <a href="{{ route('photos.user-comments') }}" class="badge bg-adaptive">{{ __('main.comments') }}</a>
    @endif

    {{ __('main.all') }}:
    <a href="{{ route('photos.albums') }}" class="badge bg-adaptive">{{ __('photos.albums') }}</a>
    <a href="{{ route('photos.all-comments') }}" class="badge bg-adaptive">{{ __('main.comments') }}</a>
    <hr>

    @if ($photos->isNotEmpty())
        <div class="sort-links border-bottom pb-3 mb-3">
            {{ __('main.sort') }}:
            @foreach ($sorting as $key => $option)
                <a href="{{ route('photos.index', ['sort' => $key, 'order' => $option['inverse'] ?? 'desc']) }}" class="badge bg-{{ $option['badge'] ?? 'adaptive' }}">
                    {{ $option['label'] }}{{ $option['icon'] ?? '' }}
                </a>
            @endforeach
        </div>

        @foreach ($photos as $photo)
            <div class="section mb-3 shadow">
                <div class="section-header d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="section-title">
                            <i class="fa fa-image"></i>
                            <a href="{{ route('photos.view', ['id' => $photo->id]) }}">{{ $photo->title }}</a>
                        </div>
                    </div>

                    <div class="text-end section-action js-rating">
                        @if (getUser() && getUser('id') !== $photo->user_id)
                            <a class="post-rating-down<?= $photo->vote === '-' ? ' active' : '' ?>" href="#" onclick="return changeRating(this);" data-id="{{ $photo->id }}" data-type="{{ $photo->getMorphClass() }}" data-vote="-" data-token="{{ csrf_token() }}"><i class="fa fa-arrow-down"></i></a>
                        @endif
                        <b>{{ formatNum($photo->rating) }}</b>
                        @if (getUser() && getUser('id') !== $photo->user_id)
                            <a class="post-rating-up<?= $photo->vote === '+' ? ' active' : '' ?>" href="#" onclick="return changeRating(this);" data-id="{{ $photo->id }}" data-type="{{ $photo->getMorphClass() }}" data-vote="+" data-token="{{ csrf_token() }}"><i class="fa fa-arrow-up"></i></a>
                        @endif
                    </div>
                </div>

                <div class="section-content">
                    @include('app/_image_viewer', ['model' => $photo])

                    @if ($photo->text)
                        <div class="section-message">
                            {{ bbCode($photo->text) }}
                        </div>
                    @endif

                    {{ __('main.added') }}: {{ $photo->user->getProfile() }} <small class="section-date text-muted fst-italic">{{ dateFixed($photo->created_at) }}</small><br>
                    <a href="{{ route('photos.comments', ['id' => $photo->id]) }}">{{ __('main.comments') }}</a> <span class="badge bg-adaptive">{{ $photo->count_comments }}</span>
                </div>
            </div>
        @endforeach

        {{ $photos->links() }}

        <div class="mb-3">
            {{ __('photos.total_photos') }}: <b>{{ $photos->total() }}</b>
        </div>
    @else
        {{ showError(__('photos.empty_photos')) }}
    @endif
@stop
