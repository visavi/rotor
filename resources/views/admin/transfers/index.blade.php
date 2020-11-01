@extends('layout')

@section('title', __('index.cash_transactions'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.cash_transactions') }}</li>
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

                <a href="/admin/transfers/view?user={{ $data->user->login }}">{{ __('transfers.all_transfers') }}</a>
            </div>

            <div>
                {{ __('transfers.transfer_for') }}: {!! $data->recipientUser->getProfile() !!}<br>
                {{ __('main.amount') }}: {{ plural($data->total, setting('moneyname')) }}<br>
                {{ __('main.comment') }}: {!! bbCode($data->text) !!}<br>
            </div>
        @endforeach

        {{ $transfers->links() }}

        <div class="section-form p-3 shadow">
            <form action="/admin/transfers/view" method="get">
                <div class="form-inline">
                    <div class="form-group{{ hasError('user') }}">
                        <input type="text" class="form-control" id="user" name="user" maxlength="20" value="{{ getInput('user') }}" placeholder="{{ __('main.user_login') }}" required>
                    </div>

                    <button class="btn btn-primary">{{ __('main.search') }}</button>
                </div>
                <div class="invalid-feedback">{{ textError('user') }}</div>
            </form>
        </div>

        {{ __('main.total') }}: <b>{{ $transfers->total() }}</b><br><br>

    @else
        {!! showError(__('transfers.empty_transfers')) !!}
    @endif
@stop
