@extends('layout')

@section('title')
    Денежные операции {{ $user->login }}
@stop

@section('content')

    <h1>Денежные операции {{ $user->login }}</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">Панель</a></li>
            <li class="breadcrumb-item"><a href="/admin/transfers">Денежные операции</a></li>
            <li class="breadcrumb-item active">Денежные операции {{ $user->login }}</li>
        </ol>
    </nav>

    @if ($transfers->isNotEmpty())

        @foreach ($transfers as $data)
            <div class="b">
                <div class="img">
                    {!! userAvatar($data->user) !!}
                    {!! userOnline($data->user) !!}
                </div>

                <b>{!! profile($data->user) !!}</b>

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
@stop
