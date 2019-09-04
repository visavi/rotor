@extends('layout')

@section('title')
    {{ __('mails.password_recovery') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ __('mails.password_recovery') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <b>{{ __('mails.successful_recovery') }}</b><br>
    {{ __('mails.details') }}:<br><br>

    {{ __('mails.login') }}: <b>{{ $login }}</b><br>
    {{ __('mails.password') }}: <b>{{ $password }}</b><br><br>

    <p>
        {{ __('mails.restore_text1') }}<br>
        {{ __('mails.restore_text2') }}<br>
    </p>
@stop
