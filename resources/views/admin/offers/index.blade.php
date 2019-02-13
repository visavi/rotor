@extends('layout')

@section('title')
    Предложения / Проблемы
@stop

@section('header')
    @if (getUser())
        <div class="float-right">
            <a class="btn btn-success" href="/offers/create?type={{ $type }}">Добавить</a><br>
        </div><br>
    @endif

    <h1>Предложения / Проблемы</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('main.panel') }}</a></li>
            <li class="breadcrumb-item active">Предложения / Проблемы</li>
            <li class="breadcrumb-item"><a href="/offers/{{ $type }}?page={{ $page->current }}">Обзор</a></li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($type === 'offer')
        <a class="btn btn-primary btn-sm" href="/admin/offers/offer">Предложения <span class="badge badge-light">{{ $page->total }}</span></a>
        <a class="btn btn-light btn-sm" href="/admin/offers/issue">Проблемы <span class="badge badge-light">{{ $page->otherTotal }}</span></a>
    @else
        <a class="btn btn-light btn-sm" href="/admin/offers/offer">Предложения <span class="badge badge-light">{{ $page->otherTotal }}</span></a>
        <a class="btn btn-primary btn-sm" href="/admin/offers/issue">Проблемы <span class="badge badge-light">{{ $page->total }}</span></a>
    @endif


    <br>Сортировать:
    <?php $active = ($order === 'rating') ? 'success' : 'light'; ?>
    <a href="/admin/offers/{{ $type }}?sort=rating" class="badge badge-{{ $active }}">Голоса</a>

    <?php $active = ($order === 'created_at') ? 'success' : 'light'; ?>
    <a href="/admin/offers/{{ $type }}?sort=time" class="badge badge-{{ $active }}">Дата</a>

    <?php $active = ($order === 'status') ? 'success' : 'light'; ?>
    <a href="/admin/offers/{{ $type }}?sort=status" class="badge badge-{{ $active }}">Статус</a>

    <?php $active = ($order === 'count_comments') ? 'success' : 'light'; ?>
    <a href="/admin/offers/{{ $type }}?sort=comments" class="badge badge-{{ $active }}">Комментарии</a>
    <hr>

    @if ($offers->isNotEmpty())

        <form action="/admin/offers/delete?type={{ $type }}&amp;page={{ $page->current }}" method="post">
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
                        Добавлено: {!! $data->user->getProfile() !!} ({{ dateFixed($data->created_at) }})<br>
                        <a href="/offers/comments/{{ $data->id }}">Комментарии</a> ({{ $data->count_comments }})
                        <a href="/offers/end/{{ $data['id'] }}">&raquo;</a></div>
                @endforeach

            <div class="float-right">
                <button class="btn btn-sm btn-danger">Удалить выбранное</button>
            </div>
        </form>

        {!! pagination($page) !!}

        Всего записей: <b>{{ $page->total }}</b><br><br>
    @else
        {!! showError('Записей еще нет!') !!}
    @endif

    @if (isAdmin('boss'))
        <i class="fa fa-sync"></i> <a href="/admin/offers/restatement?token={{ $_SESSION['token'] }}">Пересчитать</a><br>
    @endif
@stop
