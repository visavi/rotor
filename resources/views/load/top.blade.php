@extends('layout')

@section('title')
    Топ популярных файлов (Стр. {{ $page['current'] }})
@stop

@section('content')

    <h1>Топ популярных файлов</h1>

    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
        <li class="breadcrumb-item"><a href="/load">Загрузки</a></li>
        <li class="breadcrumb-item active">Топ файлов</li>
    </ol>

    Сортировать:

    <?php $active = ($order === 'loads') ? 'success' : 'light'; ?>
    <a href="/down/top?sort=loads" class="badge badge-{{ $active }}">Скачивания</a>

    <?php $active = ($order === 'rated') ? 'success' : 'light'; ?>
    <a href="/down/top?sort=rated" class="badge badge-{{ $active }}">Оценки</a>

    <?php $active = ($order === 'count_comments') ? 'success' : 'light'; ?>
    <a href="/down/top?sort=comments" class="badge badge-{{ $active }}">Комментарии</a>
    <hr>

    @if ($downs->isNotEmpty())

        @foreach ($downs as $data)
            <?php $folder = $data->category->folder ? $data->category->folder.'/' : '' ?>
            <?php $filesize = $data->link ? formatFileSize(UPLOADS.'/files/'.$folder.$data->link) : 0; ?>

            <div class="b">
                <i class="fa fa-file"></i>
                <b><a href="/down/{{ $data->id }}">{{ $data->title }}</a></b> ({{ $filesize }})
            </div>

            <div>
                Скачиваний: {{ $data->loads }}<br>

                <?php $rating = $data->rated ? round($data->rating / $data->rated, 1) : 0; ?>

                Рейтинг: <b>{{ $rating }}</b> (Голосов: {{ $data->rated }})<br>
                <a href="/down/comments/{{ $data->id }}">Комментарии</a> ({{ $data->count_comments }})
                <a href="/down/end/{{ $data->id }}">&raquo;</a>
            </div>
        @endforeach

        {!! pagination($page) !!}
    @else
        @if (! $category->closed)
            {!! showError('В данной категории еще нет файлов!') !!}
        @endif
    @endif
@stop
