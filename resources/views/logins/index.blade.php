@extends('layout')

@section('title', __('index.auth_history'))

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
        @foreach ($logins as $data)
            <div class="section mb-3 shadow">
                <h5>
                    <i class="fa fa-sign-in-alt"></i> {{ $data->getType() }}
                    <small class="section-date text-muted font-italic">{{ dateFixed($data->created_at) }}</small>
                </h5>

                <div class="section-body border-top">
                    <div class="small text-muted font-italic mt-2">
                        Browser: {{ $data->brow }} /
                        IP: {{ $data->ip }}
                    </div>
                </div>
            </div>
        @endforeach
    @else
        {{ showError(__('logins.empty_history')) }}
    @endif

    {{ $logins->links() }}
@stop
