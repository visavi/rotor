@extends('layout')

@section('title')
    {{ trans('photos.top_photos') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/photos">{{ trans('photos.title') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('photos.top_photos') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($photos->isNotEmpty())

        {{ trans('main.sort') }}:
        <?php $active = ($order === 'rating') ? 'success' : 'light'; ?>
        <a href="/photos/top?sort=rating" class="badge badge-{{ $active }}">{{ trans('main.rating') }}</a>

        <?php $active = ($order === 'count_comments') ? 'success' : 'light'; ?>
        <a href="/photos/top?sort=comments" class="badge badge-{{ $active }}">{{ trans('main.comments') }}</a>
        <hr>

        @foreach ($photos as $photo)
            <div class="b">
                <i class="fa fa-image"></i>
                <b><a href="/photos/{{ $photo->id }}">{{ $photo->title }}</a></b> ({!! formatNum($photo->rating) !!})
            </div>

            <div>
                <?php $countFiles = $photo->files->count() ?>
                <div id="myCarousel{{ $loop->iteration }}" class="carousel slide w-75" data-ride="carousel">
                    @if ($countFiles > 1)
                        <ol class="carousel-indicators">
                            @for ($i = 0; $i < $countFiles; $i++)
                                <li data-target="#myCarousel{{ $loop->iteration }}" data-slide-to="{{ $i }}"{!! empty($i) ? ' class="active"' : '' !!}></li>
                            @endfor
                        </ol>
                    @endif

                    <div class="carousel-inner">
                        @foreach ($photo->files as $file)
                            <div class="carousel-item{{ $loop->first ? ' active' : '' }}">
                                {!! resizeImage($file->hash, ['alt' => $photo->title, 'class' => 'd-block w-100']) !!}
                            </div>
                        @endforeach
                    </div>

                    @if ($countFiles > 1)
                        <a class="carousel-control-prev" href="#myCarousel{{ $loop->iteration }}" role="button" data-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="sr-only">Previous</span>
                        </a>
                        <a class="carousel-control-next" href="#myCarousel{{ $loop->iteration }}" role="button" data-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="sr-only">Next</span>
                        </a>
                    @endif
                </div>

                <br>{!! bbCode($photo->text) !!}<br>

                    {{ trans('main.added') }}: {!! $photo->user->getProfile() !!} ({{ dateFixed($photo->created_at) }})<br>
                <a href="/photos/comments/{{ $photo->id }}">{{ trans('main.comments') }}</a> ({{ $photo->count_comments }})
                <a href="/photos/end/{{ $photo->id }}">&raquo;</a>
            </div>
        @endforeach

        {!! pagination($page) !!}
    @else
        {!! showError(trans('photos.empty_photos')) !!}
    @endif
@stop
