@extends('layout')

@section('title')
    Денежные операции {{ $user->login }}
@stop

@section('content')

    <h1>Денежные операции {{ $user->login }}</h1>

    @if ($transfers->isNotEmpty())

        @foreach ($transfers as $data)
            <div class="b">
                <div class="img">{!! userAvatar($data->user) !!}</div>
                <b>{!! profile($data->user) !!}</b> {!! userOnline($data->user) !!}

                <small>({{ dateFixed($data->created_at) }})</small><br>
            </div>

            <div>
                Кому: {!! profile($data->recipientUser) !!}<br>
                Сумма: {{ plural($data->total, setting('moneyname')) }}<br>
                Комментарий: {!! bbCode($data->text) !!}<br>
            </div>
        @endforeach

        {!! pagination($page) !!}

    Всего операций: <b>{{ $page->total }}</b><br><br>

    @else
        {!! showError('Истории операций еще нет!') !!}
    @endif

    <i class="fa fa-arrow-circle-left"></i> <a href="/admin/transfers">Вернуться</a><br>
    <i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>
@stop
