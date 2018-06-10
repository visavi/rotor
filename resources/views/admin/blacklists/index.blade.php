@extends('layout')

@section('title')
    Черный список
@stop

@section('content')

    <h1>Черный список</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">Панель</a></li>
            <li class="breadcrumb-item active">Черный список</li>
        </ol>
    </nav>

    <?php $active = ($type == 'email') ? 'success' : 'light'; ?>
    <a href="/admin/blacklists?type=email" class="badge badge-{{ $active }}">Email</a>
    <?php $active = ($type == 'login') ? 'success' : 'light'; ?>
    <a href="/admin/blacklists?type=login" class="badge badge-{{ $active }}">Логины</a>
    <?php $active = ($type == 'domain') ? 'success' : 'light'; ?>
    <a href="/admin/blacklists?type=domain" class="badge badge-{{ $active }}">Домены</a>
    <br><br>

    @if ($lists->isNotEmpty())

        <form action="/admin/blacklist/delete?type={{ $type }}&amp;page={{ $page->current }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            @foreach ($lists as $list)
                <div class="b">
                    <input type="checkbox" name="del[]" value="{{ $list->id }}">

                    <i class="fa fa-pencil-alt"></i> <b>{{ $list->value }}</b>
                </div>
                <div>
                    Добавлено: {!! $list->user->getProfile() !!}<br>
                    Время: {{ dateFixed($list->created_at) }}
                </div>
            @endforeach

            <button class="btn btn-sm btn-danger">Удалить выбранное</button>
        </form>

        {!! pagination($page) !!}

    @else
        {!! showError('Cписок еще пуст!') !!}
    @endif

    <div class="form">
        <form action="/admin/blacklists?type={{ $type }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-inline">
                <div class="form-group{{ hasError('value') }}">
                    <input type="text" class="form-control" id="value" name="value" maxlength="100" value="{{ getInput('value') }}" placeholder="Введите запись" required>
                </div>

                <button class="btn btn-primary">Добавить</button>
            </div>
            {!! textError('value') !!}
        </form>
    </div><br>

    Всего в списке: <b>{{ $page->total }}</b><br>
@stop
