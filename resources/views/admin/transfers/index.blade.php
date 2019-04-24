@extends('layout')

@section('title')
    Денежные операции
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item active">Денежные операции</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($transfers->isNotEmpty())

        @foreach ($transfers as $data)
            <div class="b">
                <div class="img">
                    {!! $data->user->getAvatar() !!}
                    {!! $data->user->getOnline() !!}
                </div>

                <b>{!! $data->user->getProfile() !!}</b>

                <small>({{ dateFixed($data->created_at) }})</small><br>

                <a href="/admin/transfers/view?user={{ $data->user->login }}">Все переводы</a>
            </div>

            <div>
                Кому: {!! $data->recipientUser->getProfile() !!}<br>
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
@stop
