@extends('layout')

@section('title')
    Управление новостями
@stop

@section('content')

    <div class="float-right">
        <a class="btn btn-success" href="/admin/news/create">Добавить новость</a>
    </div><br>

    <h1>Управление новостями</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">Панель</a></li>
            <li class="breadcrumb-item active">Новости</li>
            <li class="breadcrumb-item"><a href="/news">Обзор новостей</a></li>
        </ol>
    </nav>

    @if ($news->isNotEmpty())

        <form action="/admin/news/delete?page={{ $page->current}} " method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            @foreach ($news as $data)

                <div class="b">
                    <div class="float-right">
                        @if ($data->top)
                            <div class="right"><span style="color:#ff0000">На главной</span></div>
                        @endif
                    </div>

                    <i class="fa {{ $data->getIcon() }} text-muted"></i>

                    <b><a href="/news/{{ $data->id }}">{{ $data->title }}</a></b><small> ({{ dateFixed($data->created_at) }})</small><br>
                    <input type="checkbox" name="del[]" value="{{ $data->id }}">
                    <a href="/admin/news/edit/{{ $data->id }}?page={{ $page->current }}">Редактировать</a>
                </div>

                @if ($data->image)
                    <div class="img">
                        <a href="/uploads/news/{{ $data->image }}">{!! resizeImage('/uploads/news/' . $data->image, ['size' => 100, 'alt' => $data->title]) !!}</a>
                    </div>
                @endif

                <div class="clearfix">{!! bbCode($data->shortText()) !!}</div>

                <div>Добавлено: {!! profile($data->user) !!}<br>
                    <a href="/news/comments/{{  $data->id }}">Комментарии</a> ({{ $data->count_comments }})
                    <a href="/news/end/{{ $data->id }}">&raquo;</a>
                </div>
            @endforeach

            <button class="btn btn-sm btn-danger">Удалить выбранное</button>
        </form>

        {!! pagination($page) !!}

        Всего новостей: <b>{{ $news->count() }}</b><br><br>
    @else
        {!! showError('Новостей еще нет!') !!}
    @endif

    @if (isAdmin('boss'))
        <i class="fa fa-sync"></i> <a href="/admin/news/restatement?token={{ $_SESSION['token'] }}">Пересчитать</a><br>
    @endif
@stop
