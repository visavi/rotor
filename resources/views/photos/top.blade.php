@extends('layout')

@section('title', __('photos.top_photos'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/photos">{{ __('index.photos') }}</a></li>
            <li class="breadcrumb-item active">{{ __('photos.top_photos') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($photos->isNotEmpty())

        {{ __('main.sort') }}:
        <?php $active = ($order === 'rating') ? 'success' : 'light text-dark'; ?>
        <a href="/photos/top?sort=rating" class="badge bg-{{ $active }}">{{ __('main.rating') }}</a>

        <?php $active = ($order === 'count_comments') ? 'success' : 'light text-dark'; ?>
        <a href="/photos/top?sort=comments" class="badge bg-{{ $active }}">{{ __('main.comments') }}</a>
        <hr>

        @foreach ($photos as $photo)
            <div class="section mb-3 shadow">
                <div class="section-title">
                    <i class="fa fa-image"></i>
                    <a href="/photos/{{ $photo->id }}">{{ $photo->title }}</a> ({{ formatNum($photo->rating) }})
                </div>

                <div class="section-content">
                    @include('app/_viewer', ['model' => $photo])

                    <div class="section-message">
                        {{ bbCode($photo->text) }}
                    </div>

                        {{ __('main.added') }}: {{ $photo->user->getProfile() }} ({{ dateFixed($photo->created_at) }})<br>
                    <a href="/photos/comments/{{ $photo->id }}">{{ __('main.comments') }}</a> ({{ $photo->count_comments }})
                    <a href="/photos/end/{{ $photo->id }}">&raquo;</a>
                </div>
            </div>
        @endforeach
    @else
        {{ showError(__('photos.empty_photos')) }}
    @endif

    {{ $photos->links() }}
@stop
