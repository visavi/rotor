@extends('layout')

@section('title')
    Денежные операции {{ $user->login }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/transfers">Денежные операции</a></li>
            <li class="breadcrumb-item active">Денежные операции {{ $user->login }}</li>
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
            </div>

            <div>
                Кому: {!! $data->recipientUser->getProfile() !!}<br>
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
