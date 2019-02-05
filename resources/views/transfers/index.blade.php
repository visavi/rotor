@extends('layout')

@section('title')
    {{ trans('transfers.title') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/menu">{{ trans('common.menu') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('transfers.title') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    {{ trans('transfers.in_stock') }}: {{ plural(getUser('money'), setting('moneyname')) }}<br><br>

    @if (getUser('point') >= setting('sendmoneypoint'))
        <div class="form">
            <form action="/transfers/send" method="post">
                <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

                @if ($user)
                    <i class="fa fa-money-bill-alt"></i> {{ trans('transfers.transfer_for') }} <b>{{ $user->login }}</b>:<br><br>
                    <input type="hidden" name="user" value="{{ $user->login }}">
                @else
                    <div class="form-group{{ hasError('user') }}">
                        <label for="inputUser">{{ trans('transfers.user_login') }}:</label>
                        <input name="user" class="form-control" id="inputUser" maxlength="20" placeholder="{{ trans('transfers.user_login') }}" value="{{ getInput('user') }}" required>
                        {!! textError('user') !!}
                    </div>
                @endif

                <div class="form-group{{ hasError('money') }}">
                    <label for="inputMoney">{{ trans('transfers.sum') }}:</label>
                    <input name="money" class="form-control" id="inputMoney" placeholder="{{ trans('transfers.sum') }}" value="{{ getInput('money') }}" required>
                    {!! textError('money') !!}
                </div>

                <div class="form-group{{ hasError('msg') }}">
                    <label for="msg">{{ trans('transfers.comment') }}:</label>
                    <textarea class="form-control markItUp" maxlength="{{ setting('comment_length') }}" id="msg" rows="5" name="msg" placeholder="{{ trans('transfers.comment') }}">{{ getInput('msg') }}</textarea>
                    <span class="js-textarea-counter"></span>
                    {!! textError('msg') !!}
                </div>

                <button class="btn btn-primary">{{ trans('transfers.transfer') }}</button>
            </form>
        </div><br>
    @else
       {!! showError(trans('transfers.error', ['points' => plural(setting('sendmoneypoint'), setting('scorename'))])) !!}
    @endif

@stop
