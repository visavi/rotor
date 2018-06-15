@extends('layout')

@section('title')
    Предложения / Проблемы
@stop

@section('content')

    @if (getUser())
        <div class="float-right">
            <a class="btn btn-success" href="/offers/create?type={{ $type }}">Добавить</a>
        </div><br>
    @endif

    <h1>Предложения / Проблемы</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">Предложения / Проблемы</li>

            @if (isAdmin('admin'))
                <li class="breadcrumb-item"><a href="/admin/offers/{{ $type }}?page={{ $page->current }}">Управление</a></li>
            @endif
        </ol>
    </nav>

    @if ($type === 'offer')
        <a class="btn btn-primary btn-sm" href="/offers/offer">Предложения <span class="badge badge-light">{{ $page->total }}</span></a>
        <a class="btn btn-light btn-sm" href="/offers/issue">Проблемы <span class="badge badge-light">{{ $page->otherTotal }}</span></a>
    @else
        <a class="btn btn-light btn-sm" href="/offers/offer">Предложения <span class="badge badge-light">{{ $page->otherTotal }}</span></a>
        <a class="btn btn-primary btn-sm" href="/offers/issue">Проблемы <span class="badge badge-light">{{ $page->total }}</span></a>
    @endif

    <br>Сортировать:
    <?php $active = ($order === 'rating') ? 'success' : 'light'; ?>
    <a href="/offers/{{ $type }}?sort=rating" class="badge badge-{{ $active }}">Голоса</a>

    <?php $active = ($order === 'created_at') ? 'success' : 'light'; ?>
    <a href="/offers/{{ $type }}?sort=time" class="badge badge-{{ $active }}">Дата</a>

    <?php $active = ($order === 'status') ? 'success' : 'light'; ?>
    <a href="/offers/{{ $type }}?sort=status" class="badge badge-{{ $active }}">Статус</a>

    <?php $active = ($order === 'count_comments') ? 'success' : 'light'; ?>
    <a href="/offers/{{ $type }}?sort=comments" class="badge badge-{{ $active }}">Комментарии</a>
    <hr>

    @if ($offers->isNotEmpty())

        @foreach ($offers as $data)
            <div class="b">
                <i class="fa fa-file"></i>
                <b><a href="/offers/{{ $data->id }}">{{ $data->title }}</a></b> (Голосов: {{ $data->rating }})<br>
                {!! $data->getStatus() !!}
            </div>

            <div>{!! bbCode($data->text) !!}<br>
            Добавлено: {!! $data->user->getProfile() !!} ({{ dateFixed($data->created_at) }})<br>
            <a href="/offers/comments/{{ $data->id }}">Комментарии</a> ({{ $data->count_comments }})
            <a href="/offers/end/{{ $data['id'] }}">&raquo;</a></div>
        @endforeach

        {!! pagination($page) !!}

        Всего записей: <b>{{ $page->total }}</b><br><br>
    @else
        {!! showError('Записей еще нет!') !!}
    @endif
@stop
