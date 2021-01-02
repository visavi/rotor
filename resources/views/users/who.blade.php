@extends('layout')

@section('title', __('index.who_online'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ __('index.who_online') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="section mb-3 shadow">
        <div class="section-title">
            {{ __('index.users') }}
        </div>

        <div class="section-body">
            @if ($online->isNotEmpty())
                @foreach ($online as $key => $value)
                    {{ $comma = (empty($key)) ? '' : ', ' }}
                    {!! $value->user->getGender() !!} {!! $value->user->getProfile() !!}
                @endforeach

                <div class="mt-3">
                    {{ __('main.total_users') }}: {{ $online->count() }}
                </div>
            @else
                {!! showError(__('main.empty_users')) !!}
            @endif
        </div>
    </div>

    <div class="section mb-3 shadow">
        <div class="section-title">
            {{ __('users.birthdays') }}
        </div>

        <div class="section-body">
            @if ($birthdays->isNotEmpty())
                @foreach ($birthdays as $key => $value)
                    {{ $comma = (empty($key)) ? '' : ', ' }}
                    {!! $value->getGender() !!} {!! $value->getProfile() !!}
                @endforeach

                <div class="mt-3">
                    {{ __('users.total_birthdays') }}: {{ $birthdays->count() }}
                </div>
            @else
                {!! showError(__('users.empty_birthdays')) !!}
            @endif
        </div>
    </div>

    <div class="section mb-3 shadow">
        <div class="section-title">
            {{ __('users.novices') }}
        </div>

        <div class="section-body">
            @if ($novices->isNotEmpty())
                @foreach ($novices as $key => $value)
                    {{ $comma = (empty($key)) ? '' : ', ' }}
                    {!! $value->getGender() !!} {!! $value->getProfile() !!}
                @endforeach

                <div class="mt-3">
                    {{ __('users.total_novices') }}: {{ $novices->count() }}
                </div>
            @else
                {!! showError(__('users.empty_novices')) !!}
            @endif
        </div>
    </div>
@stop
