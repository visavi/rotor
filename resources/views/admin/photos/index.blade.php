@extends('layout')

@section('title')
    Галерея
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
            <li class="breadcrumb-item"><a href="/admin">Панель</a></li>
            <li class="breadcrumb-item active">Галерея</li>
            <li class="breadcrumb-item"><a href="/photos?page={{ $page->current }}">Обзор</a></li>
        </ol>
    </nav>

    @if ($photos->isNotEmpty())

        <form action="/admin/photos/delete?page={{ $page->current }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            @foreach ($photos as $photo)
                <div class="b">
                    <i class="fa fa-image"></i>
                    <b><a href="/photos/{{ $photo->id }}">{{ $photo->title }}</a></b>

                    <div class="float-right">
                        <a href="/admin/photos/edit/{{ $photo->id }}?page={{ $page->current }}"><i class="fas fa-pencil-alt text-muted"></i></a>
                        <input type="checkbox" name="del[]" value="{{ $photo->id }}">
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

                    Добавлено: {!! $photo->user->getProfile() !!} ({{ dateFixed($photo->created_at) }})<br>
                    <a href="/photos/comments/{{ $photo->id }}">Комментарии</a> ({{ $photo->count_comments }})
                    <a href="/photos/end/{{ $photo->id }}">&raquo;</a>
                </div>
            @endforeach

            <div class="float-right">
                <button class="btn btn-sm btn-danger">Удалить выбранное</button>
            </div>
        </form>

        {!! pagination($page) !!}

        Всего фотографий: <b>{{ $page->total }}</b><br><br>

        @if (isAdmin('boss'))
            <i class="fa fa-sync"></i> <a href="/admin/photos/restatement?token={{ $_SESSION['token'] }}">Пересчитать</a><br>
        @endif
    @else
        {!! showError('Фотографий еще нет!') !!}
    @endif
@stop
