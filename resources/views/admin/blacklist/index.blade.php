@extends('layout')

@section('title')
    Черный список
@stop

@section('content')

    <h1>Черный список</h1>

    <?php $active = ($type == 'email') ? 'success' : 'light'; ?>
    <a href="/admin/blacklist?type=email" class="badge badge-{{ $active }}">Email</a>
    <?php $active = ($type == 'login') ? 'success' : 'light'; ?>
    <a href="/admin/blacklist?type=login" class="badge badge-{{ $active }}">Логины</a>
    <?php $active = ($type == 'domain') ? 'success' : 'light'; ?>
    <a href="/admin/blacklist?type=domain" class="badge badge-{{ $active }}">Домены</a>
    <br><br>



    @if ($lists->isNotEmpty())


        <form action="/admin/blacklist/delete?type={{ $type }}&amp;page={{ $page['current'] }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            @foreach ($lists as $list)
                <div class="b">
                    <input type="checkbox" name="del[]" value="{{ $list->id }}">

                    <i class="fa fa-pencil-alt"></i> <b>{{ $list->value }}</b>
                </div>
                <div>
                    Добавлено: {!! profile($list->user) !!}<br>
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
        <form action="/admin/blacklist?type={{ $type }}" method="post">
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

    Всего в списке: <b>{{ $page['total'] }}</b><br><br>

    <i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>
@stop
