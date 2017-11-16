@extends('layout')

@section('title')
    Предложения и проблемы
@stop

@section('content')

    @if (getUser())
        <div class="float-right">
            <a class="btn btn-success" href="/offers/create">Добавить</a><br>
        </div>
    @endif

    <h1>Предложения и проблемы</h1>

    <i class="fa fa-book"></i>

    @if ($type == 'offer')
        <b>Предложения</b> ({{ $page['total'] }}) / <a href="/offers/issue">Проблемы</a> ({{ $page['otherTotal'] }})
    @else
        <a href="/offers/offer">Предложения</a> ({{ $page['otherTotal'] }}) / <b>Проблемы</b> ({{ $page['total'] }})
    @endif

    @if (isAdmin('admin'))
        / <a href="/admin/offers/{{ $type }}?page={{ $page['current'] }}">Управление</a>
    @endif

    <br>Сортировать:
    @if ($order == 'rating')
        <b>Голоса</b> /
    @else
        <a href="/offers/{{ $type }}?sort=rating">Голоса</a> /
    @endif

    @if ($order == 'created_at')
        <b>Дата</b> /
    @else
        <a href="/offers/{{ $type }}?sort=time">Дата</a> /
    @endif

    @if ($order == 'status')
        <b>Статус</b> /
    @else
        <a href="/offers/{{ $type }}?sort=status">Статус</a> /
    @endif

    @if ($order == 'comments')
        <b>Комментарии</b>
    @else
        <a href="/offers/{{ $type }}?sort=comments">Комментарии</a>
    @endif
    <hr>

    @if ($offers->isNotEmpty())

        @foreach ($offers as $data)
            <div class="b">
                <i class="fa fa-file-o"></i>
                <b><a href="/offers/{{ $data->id }}">{{ $data->title }}</a></b> (Голосов: {{ $data->rating }})<br>
                {!! $data->getStatus() !!}
            </div>

            <div>{!! bbCode($data->text) !!}<br>
            Добавлено: {!! profile($data->user) !!} ({{ dateFixed($data->created_at) }})<br>
            <a href="/offers/{{ $data->id }}/comments">Комментарии</a> ({{ $data->comments }})
            <a href="/offers/{{ $data['id'] }}/end">&raquo;</a></div>
        @endforeach

        {!! pagination($page) !!}

        Всего записей: <b>{{ $page['total'] }}</b><br><br>
    @else
        {!! showError('Записей еще нет!') !!}
    @endif
@stop
