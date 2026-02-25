@extends('layout')

@section('title', __('index.logs_visits'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.logs_visits') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($logs->isNotEmpty())
        @foreach ($logs as $log)
            <div class="section mb-3 shadow">
                <div class="user-avatar">
                    {{ $log->user->getAvatar() }}
                    {{ $log->user->getOnline() }}
                </div>

                <div class="section-user d-flex align-items-start">
                    <div class="flex-grow-1">
                        {{ $log->user->getProfile() }}
                        <small class="section-date text-muted fst-italic">{{ dateFixed($log->created_at) }}</small>
                    </div>
                </div>

                <div class="section-body border-top">
                    {{ __('admin.logs.page') }}: {{ $log->request }}<br>
                    {{ __('admin.logs.referer') }}: {{ $log->referer }}<br>
                    <div class="small text-muted fst-italic mt-2">
                        {{ $log->brow }}, {{ $log->ip }}
                    </div>
                </div>
            </div>
        @endforeach

        {{ $logs->links() }}

        <form action="/admin/logs/clear" method="post">
            @csrf
            <button type="submit" class="btn btn-danger" onclick="return confirm('{{ __('admin.logs.confirm_clear') }}')"><i class="fa fa-trash-alt"></i> {{ __('main.clear') }}</button>
        </form><br>
    @else
        {{ showError(__('admin.logs.empty_logs')) }}
    @endif
@stop
