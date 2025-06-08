@extends('layout')

@section('title', __('index.cash_transactions'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.cash_transactions') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($transfers->isNotEmpty())
        @foreach ($transfers as $data)
            <div class="section mb-3 shadow">
                <div class="user-avatar">
                    {{ $data->user->getAvatar() }}
                    {{ $data->user->getOnline() }}
                </div>

                <div class="section-user d-flex align-items-center">
                    <div class="flex-grow-1">
                        {{ $data->user->getProfile() }}
                        <small class="section-date text-muted fst-italic">({{ dateFixed($data->created_at) }})</small><br>
                        <small class="fst-italic">{{ $data->user->getStatus() }}</small>
                    </div>

                    <div class="text-end">
                        <a href="/admin/transfers/view?user={{ $data->user->login }}">{{ __('transfers.all_transfers') }}</a>
                    </div>
                </div>

                <div class="section-body border-top">
                    {{ __('transfers.transfer_for') }}: {{ $data->recipientUser->getProfile() }}<br>
                    {{ __('main.amount') }}: {{ plural($data->total, setting('moneyname')) }}<br>
                    {{ __('main.comment') }}: {{ bbCode($data->text) }}<br>
                </div>
            </div>
        @endforeach

        {{ $transfers->links() }}

        <div class="section-form mb-3 shadow">
            <form action="/admin/transfers/view" method="get">
                <div class="input-group{{ hasError('user') }}">
                    <input type="text" class="form-control" id="user" name="user" maxlength="20" value="{{ getInput('user') }}" placeholder="{{ __('main.user_login') }}" required>
                    <button class="btn btn-primary">{{ __('main.search') }}</button>
                </div>
                <div class="invalid-feedback">{{ textError('user') }}</div>
            </form>
        </div>

        <div class="mb-3">
            {{ __('main.total') }}: <b>{{ $transfers->total() }}</b>
        </div>
    @else
        {{ showError(__('transfers.empty_transfers')) }}
    @endif
@stop
