@extends('layout')

@section('title')
    Поиск запроса {{ $find }}
@stop

@section('content')
    <h1>Поиск запроса {{ $find }}</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/load">Загрузки</a></li>
            <li class="breadcrumb-item"><a href="/load/search">Поиск</a></li>
            <li class="breadcrumb-item active">Поиск запроса</li>
        </ol>
    </nav>

    Найдено совпадений в описании: <b>{{ $page['total'] }}</b><br><br>

    @foreach ($downs as $data)
        <?php $folder = $data->category->folder ? $data->category->folder.'/' : '' ?>
        <?php $filesize = $data->link ? formatFileSize(UPLOADS.'/files/'.$folder.$data->link) : 0; ?>

        <div class="b">
            <i class="fa fa-file"></i>
            <b><a href="/down/{{ $data->id }}">{{ $data->title }}</a></b> ({{ $filesize }})
        </div>

        <div>

            {!! $data->cutText() !!}<br>

            Категория: <a href="/load/{{ $data->category->id }}">{{ $data->category->name }}</a><br>
            Добавил: {!! profile($data->user) !!} ({{ dateFixed($data->created_at) }})
        </div>
    @endforeach

    {!! pagination($page) !!}
@stop
