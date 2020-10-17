@extends('layout')

@section('title', __('index.photos') . ' (' . __('main.page_num', ['page' => $photos->currentPage()]) .')')

@section('header')
    @if (getUser())
        <div class="float-right">
            <a class="btn btn-success" href="/photos/create">{{ __('main.add') }}</a><br>
        </div>
    @endif

    <h1>{{ __('index.photos') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ __('index.photos') }}</li>

            @if (isAdmin())
                <li class="breadcrumb-item"><a href="/admin/photos?page={{ $photos->currentPage() }}">{{ __('main.management') }}</a></li>
            @endif
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

    @if ($photos->isNotEmpty())
        @foreach ($photos as $photo)
            <div class="b"><i class="fa fa-image"></i>
                <b><a href="/photos/{{ $photo->id }}">{{ $photo->title }}</a></b>
                ({{ __('main.rating') }}: {!! formatNum($photo->rating) !!})
            </div>

            <div>
                <?php $countFiles = $photo->files->count() ?>
                <div id="myCarousel{{ $loop->iteration }}" class="carousel slide media-file" data-ride="carousel">
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

                {{ __('main.added') }}: {!! $photo->user->getProfile() !!} ({{ dateFixed($photo->created_at) }})<br>
                <a href="/photos/comments/{{ $photo->id }}">{{ __('main.comments') }}</a> ({{ $photo->count_comments }})
                <a href="/photos/end/{{ $photo->id }}">&raquo;</a>
            </div>
        @endforeach

        <br>{{ __('photos.total_photos') }}: <b>{{ $photos->total() }}</b><br>
    @else
        {!! showError(__('photos.empty_photos')) !!}
    @endif

    {{ $photos->links() }}
@stop
