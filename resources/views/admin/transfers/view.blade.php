@extends('layout')

@section('title')
    {{ trans('index.cash_transactions') }} {{ $user->login }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/transfers">{{ trans('index.cash_transactions') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('index.cash_transactions') }} {{ $user->login }}</li>
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
                {{ trans('transfers.transfer_for') }}: {!! $data->recipientUser->getProfile() !!}<br>
                {{ trans('main.amount') }}: {{ plural($data->total, setting('moneyname')) }}<br>
                {{ trans('main.comment') }}: {!! bbCode($data->text) !!}<br>
            </div>
        @endforeach

        {!! pagination($page) !!}

        {{ trans('main.total') }}: <b>{{ $page->total }}</b><br><br>

    @else
        {!! showError(trans('transfers.empty_transfers')) !!}
    @endif
@stop
