@extends('layout')

@section('title')
    Денежные операции
@stop

@section('content')

    <h1>Денежные операции</h1>

    @if ($transfers->isNotEmpty())

        @foreach ($transfers as $data)
            <div class="b">
                <div class="img">{!! userAvatar($data->user) !!}</div>
                <b>{!! profile($data->user) !!}</b> {!! userOnline($data->user) !!}

                <small>({{ dateFixed($data->created_at) }})</small><br>

                <a href="/admin/transfers/view?user={{ $data->user->login }}">Все переводы</a>
            </div>

            <div>
                Кому: {!! profile($data->recipientUser) !!}<br>
                Сумма: {{ plural($data->total, setting('moneyname')) }}<br>
                Комментарий: {!! bbCode($data->text) !!}<br>
            </div>
        @endforeach

        {!! pagination($page) !!}

        <div class="form">
            <form action="/admin/transfers/view" method="get">
                <div class="form-inline">
                    <div class="form-group{{ hasError('user') }}">
                        <input type="text" class="form-control" id="user" name="user" maxlength="20" value="{{ getInput('user') }}" placeholder="Логин пользователя" required>
                    </div>

                    <button class="btn btn-primary">Найти</button>
                </div>
                {!! textError('user') !!}
            </form>
        </div>

        Всего операций: <b>{{ $page->total }}</b><br><br>

    @else
        {!! showError('Истории операций еще нет!') !!}
    @endif

    <i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>
@stop
