@extends('layout')

@section('title')
    {{ trans('index.cash_transactions') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('index.cash_transactions') }}</li>
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

                <a href="/admin/transfers/view?user={{ $data->user->login }}">{{ trans('transfers.all_transfers') }}</a>
            </div>

            <div>
                {{ trans('transfers.transfer_for') }}: {!! $data->recipientUser->getProfile() !!}<br>
                {{ trans('main.amount') }}: {{ plural($data->total, setting('moneyname')) }}<br>
                {{ trans('main.comment') }}: {!! bbCode($data->text) !!}<br>
            </div>
        @endforeach

        {!! pagination($page) !!}

        <div class="form">
            <form action="/admin/transfers/view" method="get">
                <div class="form-inline">
                    <div class="form-group{{ hasError('user') }}">
                        <input type="text" class="form-control" id="user" name="user" maxlength="20" value="{{ getInput('user') }}" placeholder="{{ trans('main.user_login') }}" required>
                    </div>

                    <button class="btn btn-primary">{{ trans('main.search') }}</button>
                </div>
                <div class="invalid-feedback">{{ textError('user') }}</div>
            </form>
        </div>

        {{ trans('main.total') }}: <b>{{ $page->total }}</b><br><br>

    @else
        {!! showError(trans('transfers.empty_transfers')) !!}
    @endif
@stop
