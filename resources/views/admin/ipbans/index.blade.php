@extends('layout')

@section('title', __('index.ip_ban'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.ip_ban') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <a href="/admin/errors?code=666">{{ __('admin.ipbans.history') }}</a><br>

    @if ($logs->isNotEmpty())
        <form action="/admin/ipbans/delete?page={{ $logs->currentPage() }}" method="post">
            @csrf
            @foreach ($logs as $log)
                <div class="section mb-3 shadow">
                    <div class="float-right">
                        <input type="checkbox" name="del[]" value="{{ $log->id }}">
                    </div>

                    <div class="section-header">
                        <i class="far fa-sticky-note"></i> <b>{{ $log->ip }}</b>
                    </div>

                    <div class="section-message">
                        {{ __('main.added_by') }}:
                        @if ($log->user->id)
                            {{ $log->user->getProfile() }}
                        @else
                            {{ __('main.automatically') }}
                        @endif

                        <small class="section-date text-muted font-italic">{{ dateFixed($log->created_at) }}</small>
                    </div>
                </div>
            @endforeach

            <div class="float-right">
                <button class="btn btn-sm btn-danger">{{ __('main.delete_selected') }}</button>
            </div>
        </form>

        {{ $logs->links() }}

        {{ __('main.total') }}: <b>{{ $logs->total() }}</b><br>
    @else
        {{ showError(__('admin.ipbans.empty_ip')) }}
    @endif

    <div class="py-2 my-2">
        <form action="/admin/ipbans" method="post">
            @csrf
            <div class="input-group{{ hasError('ip') }}">
                <input type="text" class="form-control" id="ip" name="ip" maxlength="39" value="{{ getInput('ip') }}" placeholder="IP-address" required>
                <div class="input-group-append">
                    <button class="btn btn-primary">{{ __('main.add') }}</button>
                </div>
            </div>
            <div class="invalid-feedback">{{ textError('ip') }}</div>
        </form>
    </div>

    @if ($logs->isNotEmpty() && isAdmin('boss'))
        <i class="fa fa-times"></i> <a href="/admin/ipbans/clear?token={{ $_SESSION['token'] }}" onclick="return confirm('{{ __('admin.ipbans.confirm_clear') }}')">{{ __('main.clear') }}</a><br>
    @endif
@stop
