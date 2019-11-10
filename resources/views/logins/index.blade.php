@extends('layout')

@section('title')
    {{ __('index.auth_history') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/menu">{{ __('main.menu') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.auth_history') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($logins->isNotEmpty())
        @foreach($logins as $data)
            <div class="b">
                @if ($data->type)
                    <i class="fa fa-sign-in-alt"></i> <b>{{ __('logins.authorization') }}</b>
                @else
                    <i class="fa fa-sync"></i> <b>{{ __('logins.autologin') }}</b>
                @endif

                <small>({{ dateFixed($data->created_at) }})</small>
            </div>
            <div>
                <span class="data">
                    Browser: {{ $data->brow }} /
                    IP: {{ $data->ip }}
                </span>
            </div>
        @endforeach
    @else
        {!! showError(__('logins.empty_history')) !!}
    @endif

    {{ $logins->links('app/_paginator') }}
@stop
