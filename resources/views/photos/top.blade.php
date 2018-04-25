@extends('layout')

@section('title')
    Топ популярных фотографий
@stop

@section('content')

    <h1>Топ популярных фотографий</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/photos">Галерея</a></li>
            <li class="breadcrumb-item active">Топ популярных фотографий</li>
        </ol>
    </nav>

    @if ($photos->isNotEmpty())

        Сортировать:
        <?php $active = ($order === 'rating') ? 'success' : 'light'; ?>
        <a href="/photos/top?sort=rating" class="badge badge-{{ $active }}">Оценки</a>

        <?php $active = ($order === 'count_comments') ? 'success' : 'light'; ?>
        <a href="/photos/top?sort=comments" class="badge badge-{{ $active }}">Комментарии</a>
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
                                <li data-target="#myCarousel{{ $loop->iteration }}" data-slide-to="{{ $i }}"{{ empty($i) ? ' class="active"' : '' }}></li>
                            @endfor
                        </ol>
                    @endif

                    <div class="carousel-inner">
                        @foreach ($photo->files as $file)
                            <div class="carousel-item{{ $loop->first ? ' active' : '' }}">
                                {!! resizeImage('/uploads/pictures/' . $file->hash, ['alt' => $photo->title, 'class' => 'd-block w-100']) !!}
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

                Добавлено: {!! profile($photo->user) !!} ({{ dateFixed($photo->created_at) }})<br>
                <a href="/photos/comments/{{ $photo->id }}">Комментарии</a> ({{ $photo->count_comments }})
                <a href="/photos/end/{{ $photo->id }}">&raquo;</a>
            </div>
        @endforeach

        {!! pagination($page) !!}
    @else
        {!! showError('Загруженных фотографий еще нет!') !!}
    @endif
@stop
