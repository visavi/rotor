@extends('layout')

@section('title', __('index.cash_transactions' . ' ' . $user->getName()))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/transfers">{{ __('index.cash_transactions') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.cash_transactions') }} {{ $user->getName() }}</li>
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
                {{ __('transfers.transfer_for') }}: {!! $data->recipientUser->getProfile() !!}<br>
                {{ __('main.amount') }}: {{ plural($data->total, setting('moneyname')) }}<br>
                {{ __('main.comment') }}: {!! bbCode($data->text) !!}<br>
            </div>
        @endforeach

        {{ __('main.total') }}: <b>{{ $transfers->total() }}</b><br><br>
    @else
        {!! showError(__('transfers.empty_transfers')) !!}
    @endif

    {{ $transfers->links() }}
@stop
