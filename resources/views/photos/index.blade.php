@extends('layout')

@section('title')
    {{ trans('photos.title') }} ({{ trans('main.page_num', ['page' => $page->current]) }})
@stop

@section('header')
    @if (getUser())
        <div class="float-right">
            <a class="btn btn-success" href="/photos/create">{{ trans('main.add') }}</a><br>
        </div><br>
    @endif

    <h1>{{ trans('photos.title') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ trans('photos.title') }}</li>

            @if (isAdmin())
                <li class="breadcrumb-item"><a href="/admin/photos?page={{ $page->current }}">{{ trans('main.management') }}</a></li>
            @endif
        </ol>
    </nav>
@stop

@section('content')
    @if (getUser())
        {{ trans('main.my') }}:
        <a href="/photos/albums/{{ getUser('login') }}">{{ trans('photos.photos') }}</a>,
        <a href="/photos/comments/active/{{ getUser('login') }}">{{ trans('main.comments') }}</a> /
    @endif

    {{ trans('main.all') }}:
    <a href="/photos/albums">{{ trans('photos.albums') }}</a>,
    <a href="/photos/comments">{{ trans('main.comments') }}</a> /
    <a href="/photos/top">{{ trans('photos.top_photos') }}</a>

    @if ($photos->isNotEmpty())
        @foreach ($photos as $photo)

            <div class="b"><i class="fa fa-image"></i>
                <b><a href="/photos/{{ $photo->id }}">{{ $photo->title }}</a></b>
                ({{ trans('main.rating') }}: {!! formatNum($photo->rating) !!})
            </div>

            <div>
                <?php $countFiles = $photo->files->count() ?>
                <div id="myCarousel{{ $loop->iteration }}" class="carousel slide" data-ride="carousel">
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
                            <a href="/photos/{{ $photo->id }}">{!! resizeImage($file->hash, ['alt' => $photo->title, 'class' => 'd-block w-100']) !!}</a>
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

                @if ($photo->text)
                    {!! bbCode($photo->text) !!}<br>
                @endif

                {{ trans('main.added') }}: {!! $photo->user->getProfile() !!} ({{ dateFixed($photo->created_at) }})<br>
                <a href="/photos/comments/{{ $photo->id }}">{{ trans('main.comments') }}</a> ({{ $photo->count_comments }})
                <a href="/photos/end/{{ $photo->id }}">&raquo;</a>
            </div>
        @endforeach

        {!! pagination($page) !!}

        {{ trans('photos.total_photos') }}: <b>{{ $page->total }}</b><br><br>

    @else
        {!! showError(trans('photos.empty_photos')) !!}
    @endif
@stop
