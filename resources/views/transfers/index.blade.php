@extends('layout')

@section('title',  __('index.money_transfer'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/menu">{{ __('main.menu') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.money_transfer') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    {{ __('transfers.in_stock') }}: {{ plural(getUser('money'), setting('moneyname')) }}<br><br>

    @if (getUser('point') >= setting('sendmoneypoint'))
        <div class="section-form shadow">
            <form action="/transfers/send" method="post">
                @csrf
                @if ($user)
                    <i class="fa fa-dollar-sign"></i> {{ __('transfers.transfer_for') }} <b>{{ $user->getName() }}</b>:<br><br>
                    <input type="hidden" name="user" value="{{ $user->login }}">
                @else
                    <div class="form-group{{ hasError('user') }}">
                        <label for="inputUser">{{ __('main.user_login') }}:</label>
                        <input name="user" class="form-control" id="inputUser" maxlength="20" placeholder="{{ __('main.user_login') }}" value="{{ getInput('user') }}" required>
                        <div class="invalid-feedback">{{ textError('user') }}</div>
                    </div>
                @endif

                <div class="form-group{{ hasError('money') }}">
                    <label for="inputMoney">{{ __('main.amount') }}:</label>
                    <input name="money" class="form-control" id="inputMoney" placeholder="{{ __('main.amount') }}" value="{{ getInput('money') }}" required>
                    <div class="invalid-feedback">{{ textError('money') }}</div>
                </div>

                <div class="form-group{{ hasError('msg') }}">
                    <label for="msg">{{ __('main.comment') }}:</label>
                    <textarea class="form-control markItUp" maxlength="{{ setting('comment_length') }}" id="msg" rows="5" name="msg" placeholder="{{ __('main.comment') }}">{{ getInput('msg') }}</textarea>
                    <div class="invalid-feedback">{{ textError('msg') }}</div>
                    <span class="js-textarea-counter"></span>
                </div>

                <button class="btn btn-primary">{{ __('transfers.transfer') }}</button>
            </form>
        </div>
    @else
       {!! showError(__('transfers.transfer_point', ['point' => plural(setting('sendmoneypoint'), setting('scorename'))])) !!}
    @endif

@stop
