@extends('layout')

@section('title')
    Галерея (Стр. {{ $page->current }})
@stop

@section('content')

    @if (getUser())
        <div class="float-right">
            <a class="btn btn-success" href="/photos/create">Добавить фото</a><br>
        </div><br>
    @endif

    <h1>Галерея</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">Галерея</li>

            @if (isAdmin())
                <li class="breadcrumb-item"><a href="/admin/photos?page={{ $page->current }}">Управление</a></li>
            @endif
        </ol>
    </nav>

    @if (getUser())
        Мои:
        <a href="/photos/albums/{{ getUser('login') }}">фото</a>,
        <a href="/photos/comments/{{ getUser('login') }}">комментарии</a> /
    @endif

    Все:
    <a href="/photos/albums">альбомы</a>,
    <a href="/photos/comments">комментарии</a> /
    <a href="/photos/top">Топ фото</a>

    @if ($photos->isNotEmpty())
        @foreach ($photos as $photo)



            <div class="b"><i class="fa fa-image"></i>
                <b><a href="/photos/{{ $photo->id }}">{{ $photo->title }}</a></b>
                (Рейтинг: {!! formatNum($photo->rating) !!})
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
                            {!! resizeImage('/uploads/photos/' . $file->hash, ['alt' => $photo->title, 'class' => 'd-block w-100']) !!}
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

                Добавлено: {!! profile($photo->user) !!} ({{ dateFixed($photo->created_at) }})<br>
                <a href="/photos/comments/{{ $photo->id }}">Комментарии</a> ({{ $photo->count_comments }})
                <a href="/photos/end/{{ $photo->id }}">&raquo;</a>
            </div>
        @endforeach

        {!! pagination($page) !!}

        Всего фотографий: <b>{{ $page->total }}</b><br><br>

    @else
        {!! showError('Фотографий нет, будь первым!') !!}
    @endif
@stop
