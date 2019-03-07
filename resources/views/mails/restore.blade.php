@extends('layout')

@section('title')
    {{ trans('mails.password_recovery') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ trans('mails.password_recovery') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <b>{{ trans('mails.successful_recovery') }}</b><br>
    {{ trans('mails.details') }}:<br><br>

    {{ trans('mails.login') }}: <b>{{ $login }}</b><br>
    {{ trans('mails.password') }}: <b>{{ $password }}</b><br><br>

    <p>
        {{ trans('mails.restore_text1') }}<br>
        {{ trans('mails.restore_text2') }}<br>
    </p>
@stop
