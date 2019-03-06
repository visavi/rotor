@extends('layout')

@section('title')
    {{ trans('logins.title') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/menu">{{ trans('main.menu') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('logins.title') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($logins->isNotEmpty())
        @foreach($logins as $data)
            <div class="b">
                @if ($data->type)
                    <i class="fa fa-sign-in-alt"></i> <b>{{ trans('logins.authorization') }}</b>
                @else
                    <i class="fa fa-sync"></i> <b>{{ trans('logins.autologin') }}</b>
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

        {!! pagination($page) !!}
    @else
        {!! showError(trans('logins.empty_history')) !!}
    @endif
@stop
