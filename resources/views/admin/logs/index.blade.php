@extends('layout')

@section('title')
    {{ trans('index.logs_visits') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('index.logs_visits') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($logs)
        @foreach ($logs as $log)
            <div class="b">
                <i class="fa fa-file"></i> <b>{!! $log->user->getProfile() !!}</b>
                 ({{  dateFixed($log->created_at) }})
            </div>
            <div>
                {{ trans('admin.logs.page') }}: {{ $log->request }}<br>
                {{ trans('admin.logs.referer') }}: {{ $log->referer }}<br>
                <small><span class="data">({{ $log->brow }}, {{ $log->ip }})</span></small>
            </div>
        @endforeach

        {!! pagination($page) !!}

        <i class="fa fa-times"></i> <a href="/admin/logs/clear?token={{ $_SESSION['token'] }}" onclick="return confirm('{{ trans('admin.logs.confirm_clear') }}')">{{ trans('main.clear') }}</a><br>

    @else
        {!! showError(trans('admin.logs.empty_logs')) !!}
    @endif
@stop
