@extends('layout')

@section('title', $photo->title)

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('photos.index') }}">{{ __('index.photos') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('photos.user-albums', ['user' => $photo->user->login]) }}">{{ __('photos.album') }} {{ $photo->user->getName() }}</a></li>
            <li class="breadcrumb-item active">{{ $photo->title }}</li>
        </ol>
    </nav>
@stop

@section('header')
    @if (getUser())
        @if (isAdmin())
            <div class="btn-group float-end">
                <button type="button" class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-wrench"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                    <a class="dropdown-item" href="{{ route('admin.photos.edit', ['id' => $photo->id]) }}">{{ __('main.edit') }}</a>
                    <a class="dropdown-item" href="{{ route('admin.photos.delete', ['id' => $photo->id, '_token' => csrf_token()]) }}" onclick="return confirm('{{ __('photos.confirm_delete_photo') }}')">{{ __('main.delete') }}</a>
                </div>
            </div>
        @elseif (getUser('id') === $photo->user->id)
            <div class="btn-group float-end">
                <button type="button" class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-wrench"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                    <a class="dropdown-item" href="{{ route('photos.edit', ['id' => $photo->id]) }}">{{ __('main.edit') }}</a>
                    <a class="dropdown-item" href="{{ route('photos.delete', ['id' => $photo->id, '_token' => csrf_token()]) }}" onclick="return confirm('{{ __('photos.confirm_delete_photo') }}')">{{ __('main.delete') }}</a>
                </div>
            </div>
        @endif
    @endif
    <h1>{{ $photo->title }}</h1>
@stop

@section('content')
    <div class="section mb-3 shadow">
        @include('app/_viewer', ['model' => $photo, 'files' => $photo->files])

        <div class="section-content">
            @if ($photo->text)
                <div class="section-message">
                    {{ bbCode($photo->text) }}
                </div>
            @endif

            <div class="my-2 js-rating">{{ __('main.rating') }}:
                @if (getUser() && getUser('id') !== $photo->user_id)
                    <a class="post-rating-down<?= $photo->vote === '-' ? ' active' : '' ?>" href="#" onclick="return changeRating(this);" data-id="{{ $photo->id }}" data-type="{{ $photo->getMorphClass() }}" data-vote="-" data-token="{{ csrf_token() }}"><i class="fa fa-arrow-down"></i></a>
                @endif
                <b>{{ formatNum($photo->rating) }}</b>
                @if (getUser() && getUser('id') !== $photo->user_id)
                    <a class="post-rating-up<?= $photo->vote === '+' ? ' active' : '' ?>" href="#" onclick="return changeRating(this);" data-id="{{ $photo->id }}" data-type="{{ $photo->getMorphClass() }}" data-vote="+" data-token="{{ csrf_token() }}"><i class="fa fa-arrow-up"></i></a>
                @endif
            </div>

            {{ __('main.added') }}: {{ $photo->user->getProfile() }} ({{ dateFixed($photo->created_at) }})<br>
                <a href="{{ route('photos.comments', ['id' => $photo->id])  }}">{{ __('main.comments') }}</a> <span class="badge bg-adaptive">{{ $photo->count_comments }}</span>
        </div>
    </div>

    <?php $nav = photoNavigation($photo->id); ?>

    @if (isset($nav['next']) || isset($nav['prev']))
        <div class="section mb-3 shadow text-center fw-bold">
            @if ($nav['next'])
                <a href="{{ route('photos.view', ['id' => $nav['next']]) }}">&laquo; {{ __('main.previous') }}</a> &nbsp;
            @endif

            @if ($nav['prev'])
                &nbsp; <a href="{{ route('photos.view', ['id' => $nav['prev']]) }}">{{ __('main.next') }} &raquo;</a>
            @endif
        </div>
    @endif
@stop
