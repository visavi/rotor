@extends('layout')

@section('title')
    Предложения / Проблемы
@stop

@section('content')

    @if (getUser())
        <div class="float-right">
            <a class="btn btn-success" href="/offers/create">Добавить</a><br>
        </div>
    @endif

    <h1>Предложения / Проблемы</h1>

    <i class="fa fa-book"></i>

    @if ($type == 'offer')
        <b>Предложения</b> ({{ $page['total'] }}) / <a href="/admin/offers/issue">Проблемы</a> ({{ $page['otherTotal'] }})
    @else
        <a href="/admin/offers/offer">Предложения</a> ({{ $page['otherTotal'] }}) / <b>Проблемы</b> ({{ $page['total'] }})
    @endif

    @if (isAdmin('admin'))
        / <a href="/offers/{{ $type }}?page={{ $page['current'] }}">Обзор</a>
    @endif

    <br>Сортировать:
    @if ($order == 'rating')
        <b>Голоса</b> /
    @else
        <a href="/admin/offers/{{ $type }}?sort=rating">Голоса</a> /
    @endif

    @if ($order == 'created_at')
        <b>Дата</b> /
    @else
        <a href="/admin/offers/{{ $type }}?sort=time">Дата</a> /
    @endif

    @if ($order == 'status')
        <b>Статус</b> /
    @else
        <a href="/admin/offers/{{ $type }}?sort=status">Статус</a> /
    @endif

    @if ($order == 'comments')
        <b>Комментарии</b>
    @else
        <a href="/admin/offers/{{ $type }}?sort=comments">Комментарии</a>
    @endif
    <hr>

    @if ($offers->isNotEmpty())

        <form action="/admin/offers/delete?type={{ $type }}&amp;page={{ $page['current'] }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

                @foreach ($offers as $data)
                    <div class="b">
                        <div class="float-right">
                            <a href="/admin/offers/reply/{{ $data->id }}"><i class="fas fa-reply text-muted"></i></a>
                            <a href="/admin/offers/edit/{{ $data->id }}"><i class="fas fa-pencil-alt text-muted"></i></a>
                            <input type="checkbox" name="del[]" value="{{ $data->id }}">
                        </div>

                        <i class="fa fa-file"></i>
                        <b><a href="/admin/offers/{{ $data->id }}">{{ $data->title }}</a></b> (Голосов: {{ $data->rating }})<br>
                        {!! $data->getStatus() !!}
                    </div>

                    <div>{!! bbCode($data->text) !!}<br>
                        Добавлено: {!! profile($data->user) !!} ({{ dateFixed($data->created_at) }})<br>
                        <a href="/offers/comments/{{ $data->id }}">Комментарии</a> ({{ $data->count_comments }})
                        <a href="/offers/end/{{ $data['id'] }}">&raquo;</a></div>
                @endforeach

            <div class="float-right">
                <button class="btn btn-sm btn-danger">Удалить выбранное</button>
            </div>
        </form>

        {!! pagination($page) !!}

        Всего записей: <b>{{ $page['total'] }}</b><br><br>
    @else
        {!! showError('Записей еще нет!') !!}
    @endif

    @if (isAdmin('boss'))
        <i class="fa fa-sync"></i> <a href="/admin/offers/restatement?token={{ $_SESSION['token'] }}">Пересчитать</a><br>
    @endif

    <i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>
@stop
