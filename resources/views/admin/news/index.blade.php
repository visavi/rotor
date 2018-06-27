@extends('layout')

@section('title')
    Новости
@stop

@section('content')

    <div class="float-right">
        <a class="btn btn-success" href="/admin/news/create">Добавить новость</a>
    </div><br>

    <h1>Новости</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">Панель</a></li>
            <li class="breadcrumb-item active">Новости</li>
            <li class="breadcrumb-item"><a href="/news">Обзор новостей</a></li>
        </ol>
    </nav>

    @if ($news->isNotEmpty())
        @foreach ($news as $data)

            <div class="b">
                <div class="float-right">
                    @if ($data->top)
                        <div class="right"><span style="color:#ff0000">На главной</span></div>
                    @endif
                </div>

                <i class="fa {{ $data->getIcon() }} text-muted"></i>

                <b><a href="/news/{{ $data->id }}">{{ $data->title }}</a></b><small> ({{ dateFixed($data->created_at) }})</small><br>

                <div class="float-right">
                    <a href="/admin/news/edit/{{ $data->id }}?page={{ $page->current }}"><i class="fas fa-pencil-alt text-muted"></i></a>
                    <a href="/admin/news/delete/{{ $data->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('{{ trans('news.confirm_delete') }}')"><i class="fas fa-times text-muted"></i></a>
                </div>

            </div>

            @if ($data->image)
                <div class="img">
                    <a href="{{ $data->image }}">{!! resizeImage($data->image, ['width' => 100, 'alt' => $data->title]) !!}</a>
                </div>
            @endif

            <div class="clearfix">{!! bbCode($data->shortText()) !!}</div>

            <div>Добавлено: {!! $data->user->getProfile() !!}<br>
                <a href="/news/comments/{{  $data->id }}">Комментарии</a> ({{ $data->count_comments }})
                <a href="/news/end/{{ $data->id }}">&raquo;</a>
            </div>
        @endforeach

        {!! pagination($page) !!}

        Всего новостей: <b>{{ $news->count() }}</b><br><br>
    @else
        {!! showError('Новостей еще нет!') !!}
    @endif

    @if (isAdmin('boss'))
        <i class="fa fa-sync"></i> <a href="/admin/news/restatement?token={{ $_SESSION['token'] }}">Пересчитать</a><br>
    @endif
@stop
