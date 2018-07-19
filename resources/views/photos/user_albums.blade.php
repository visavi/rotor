@extends('layout')

@section('title')
    Альбом {{ $user->login }} (Стр. {{ $page->current }})
@stop

@section('content')

    <h1>Альбом {{ $user->login }}</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/photos">Галерея</a></li>
            <li class="breadcrumb-item active">Альбом {{ $user->login }}</li>
        </ol>
    </nav>

    @if ($photos->isNotEmpty())

        @foreach ($photos as $photo)
            <div class="b">

                @if ($moder)
                    <div class="float-right">
                        <a href="/photos/edit/{{ $photo->id }}?page={{ $page->current }}"><i class="fa fa-pencil-alt text-muted"></i></a>
                        <a href="/photos/delete/{{ $photo->id }}?page={{ $page->current }}&amp;token={{ $_SESSION['token'] }}" onclick="return confirm('Вы подтверждаете удаление изображения?')"><i class="fa fa-times text-muted"></i></a>
                    </div>
                @endif

                <i class="fa fa-image"></i>
                <b><a href="/photos/{{ $photo->id }}">{{ $photo->title }}</a></b><br>
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

                @if ($photo->text)
                   {!! bbCode($photo->text) !!}<br>
                @endif

                Добавлено: {!! $photo->user->getProfile() !!} ({{ dateFixed($photo->created_at) }})<br>
                <a href="/photos/comments/{{ $photo->id }}">Комментарии</a> ({{ $photo->count_comments }})
            </div>
        @endforeach

        {!! pagination($page) !!}

        Всего фотографий: <b>{{ $page->total }}</b><br><br>
    @else
        {!! showError('Фотографий в альбоме еще нет!') !!}
    @endif
@stop
