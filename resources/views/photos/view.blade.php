@extends('layout')

@section('title')
    {{ $photo->title }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/photos">{{ __('index.photos') }}</a></li>
            <li class="breadcrumb-item"><a href="/photos/albums/{{ $photo->user->login }}">{{ __('photos.album') }}</a></li>
            <li class="breadcrumb-item active">{{ $photo->title }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if (isAdmin())
        <a href="/admin/photos/edit/{{ $photo->id }}">{{ __('main.edit') }}</a> /
       <a href="/admin/photos/delete/{{ $photo->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('{{ __('photos.confirm_delete_photo') }}')">{{ __('main.delete') }}</a>
    @endif

    @if ($photo->user->id === getUser('id') && ! isAdmin())
        <a href="/photos/edit/{{ $photo->id }}">{{ __('main.edit') }}</a> /
        <a href="/photos/delete/{{ $photo->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('{{ __('photos.confirm_delete_photo') }}')">{{ __('main.delete') }}</a>
    @endif

    <div>
        @foreach ($photo->files as $file)
            <a href="{{ $file->hash }}" class="gallery" data-group="{{ $photo->id }}"><img class="img-fluid" src="{{ $file->hash }}" alt="image"></a><br>
        @endforeach

        @if ($photo->text)
            {!! bbCode($photo->text) !!}<br>
        @endif

        <div class="js-rating">{{ __('main.rating') }}:
            @if (getUser() && getUser('id') !== $photo->user_id)
                <a class="post-rating-down<?= $photo->vote === '-' ? ' active' : '' ?>" href="#" onclick="return changeRating(this);" data-id="{{ $photo->id }}" data-type="{{ App\Models\Photo::class }}" data-vote="-" data-token="{{ $_SESSION['token'] }}"><i class="fa fa-thumbs-down"></i></a>
            @endif
            <span>{!! formatNum($photo->rating) !!}</span>
            @if (getUser() && getUser('id') !== $photo->user_id)
                <a class="post-rating-up<?= $photo->vote === '+' ? ' active' : '' ?>" href="#" onclick="return changeRating(this);" data-id="{{ $photo->id }}" data-type="{{ App\Models\Photo::class }}" data-vote="+" data-token="{{ $_SESSION['token'] }}"><i class="fa fa-thumbs-up"></i></a>
            @endif
        </div>

            {{ __('main.added') }}: {!! $photo->user->getProfile() !!} ({{ dateFixed($photo->created_at) }})<br>
        <a href="/photos/comments/{{ $photo->id }}">{{ __('main.comments') }}</a> ({{ $photo->count_comments }})
        <a href="/photos/end/{{ $photo->id }}">&raquo;</a>
    </div>
    <br>

    <?php $nav = photoNavigation($photo->id); ?>

    @if ($nav['next'] || $nav['prev'])
        <div class="form" style="text-align:center">
            @if ($nav['next'])
                <a href="/photos/{{ $nav['next'] }}">&laquo; {{ __('main.previous') }}</a> &nbsp;
            @endif

            @if ($nav['prev'])
                &nbsp; <a href="/photos/{{ $nav['prev'] }}">{{ __('main.next') }} &raquo;</a>
            @endif
        </div>
    @endif
@stop
