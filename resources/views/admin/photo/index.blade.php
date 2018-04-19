@extends('layout')

@section('title')
    Управление галереей
@stop

@section('content')

    @if (getUser())
        <div class="float-right">
            <a class="btn btn-success" href="/gallery/create">Добавить фото</a><br>
        </div><br>
    @endif

    <h1>Управление галереей</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">Панель</a></li>
            <li class="breadcrumb-item active">Галерея</li>
            <li class="breadcrumb-item"><a href="/gallery?page={{ $page->current }}">Обзор</a></li>
        </ol>
    </nav>

    @if ($photos->isNotEmpty())

        <form action="/admin/gallery/delete?page={{ $page->current }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            @foreach ($photos as $data)
                <div class="b">
                    <i class="fa fa-image"></i>
                    <b><a href="/gallery/{{ $data->id }}">{{ $data->title }}</a></b> ({{ formatFileSize(UPLOADS.'/pictures/'.$data->link) }})

                    <div class="float-right">
                        <a href="/admin/gallery/edit/{{ $data->id }}?page={{ $page->current }}"><i class="fas fa-pencil-alt text-muted"></i></a>
                        <input type="checkbox" name="del[]" value="{{ $data->id }}">
                    </div>
                </div>

                <div>
                    <a href="/gallery/{{ $data->id }}">{!! resizeImage('/uploads/pictures/' .  $data->link, ['alt' => $data->title]) !!}</a><br>
                    @if ($data->text)
                        {!! bbCode($data->text) !!}<br>
                    @endif

                    Добавлено: {!! profile($data->user) !!} ({{ dateFixed($data->created_at) }})<br>
                    <a href="/gallery/comments/{{ $data->id }}">Комментарии</a> ({{ $data->count_comments }})
                    <a href="/gallery/end/{{ $data->id }}">&raquo;</a>
                </div>
            @endforeach

            <div class="float-right">
                <button class="btn btn-sm btn-danger">Удалить выбранное</button>
            </div>
        </form>

        {!! pagination($page) !!}

        Всего фотографий: <b>{{ $page->total }}</b><br><br>

        @if (isAdmin('boss'))
            <i class="fa fa-sync"></i> <a href="/admin/gallery/restatement?token={{ $_SESSION['token'] }}">Пересчитать</a><br>
        @endif
    @else
        {!! showError('Фотографий еще нет!') !!}
    @endif
@stop
