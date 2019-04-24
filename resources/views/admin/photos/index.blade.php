@extends('layout')

@section('title')
    {{ trans('photos.title') }}
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
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('photos.title') }}</li>
            <li class="breadcrumb-item"><a href="/photos?page={{ $page->current }}">{{ trans('main.review') }}</a></li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($photos->isNotEmpty())
        <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

        @foreach ($photos as $photo)
            <div class="b">
                <i class="fa fa-image"></i>
                <b><a href="/photos/{{ $photo->id }}">{{ $photo->title }}</a></b>

                <div class="float-right">
                    <a href="/admin/photos/edit/{{ $photo->id }}?page={{ $page->current }}" data-toggle="tooltip" title="{{ trans('main.edit') }}"><i class="fas fa-pencil-alt text-muted"></i></a>
                    <a href="/admin/photos/delete/{{ $photo->id }}?page={{ $page->current }}&amp;token={{ $_SESSION['token'] }}" onclick="return confirm('{{ trans('photos.confirm_delete_photo') }}')" data-toggle="tooltip" title="{{ trans('main.delete') }}"><i class="fas fa-times text-muted"></i></a>
                </div>
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

        @if (isAdmin('boss'))
            <i class="fa fa-sync"></i> <a href="/admin/photos/restatement?token={{ $_SESSION['token'] }}">{{ trans('main.recount') }}</a><br>
        @endif
    @else
        {!! showError(trans('photos.empty_photos')) !!}
    @endif
@stop
