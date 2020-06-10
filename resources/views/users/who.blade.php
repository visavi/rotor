@extends('layout')

@section('title')
    {{ __('index.who_online') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ __('index.who_online') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="b"><b>{{ __('index.users') }}:</b></div>

    @if ($online->isNotEmpty())

        @foreach ($online as $key => $value)
            {{ $comma = (empty($key)) ? '' : ', ' }}
            {!! $value->user->getGender() !!} <b>{!! $value->user->getProfile() !!}</b>
        @endforeach

        <br>{{ __('main.total_users') }}: {{ $online->count() }}<br><br>
    @else
        {!! showError(__('main.empty_users')) !!}
    @endif

    <div class="b"><b>{{ __('users.birthdays') }}:</b></div>

    @if ($birthdays->isNotEmpty())

        @foreach ($birthdays as $key => $value)
            {{ $comma = (empty($key)) ? '' : ', ' }}
            {!! $value->getGender() !!} <b>{!! $value->getProfile() !!}</b>
        @endforeach

        <br>{{ __('users.total_birthdays') }}: {{ $birthdays->count() }}<br><br>
    @else
        {!! showError(__('users.empty_birthdays')) !!}
    @endif

    <div class="b"><b>{{ __('users.novices') }}:</b></div>

    @if ($novices->isNotEmpty())
        @foreach ($novices as $key => $value)
            {{ $comma = (empty($key)) ? '' : ', ' }}
            {!! $value->getGender() !!} <b>{!! $value->getProfile() !!}</b>
        @endforeach

        <br>{{ __('users.total_novices') }}: {{ $novices->count() }}<br><br>
    @else
        {!! showError(__('users.empty_novices')) !!}
    @endif
@stop
