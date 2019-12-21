@extends('layout')

@section('title')
    {{ __('index.ip_ban') }}
@stop

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
                <div class="b">
                    <input type="checkbox" name="del[]" value="{{ $log->id }}">
                    <i class="fa fa-file"></i> <b>{{ $log->ip }}</b>
                </div>

                <div>{{ __('main.added') }}:
                    @if ($log->user->id)
                        <b>{!! $log->user->getProfile() !!}</b>
                    @else
                        <b>{{ __('main.automatically') }}</b>
                    @endif

                    ({{ dateFixed($log->created_at) }})
                </div>
            @endforeach

            <button class="btn btn-sm btn-danger">{{ __('main.delete_selected') }}</button>
        </form>
        <br>{{ __('main.total') }}: <b>{{ $logs->total() }}</b><br>
    @else
        {!! showError(__('admin.ipbans.empty_ip')) !!}
    @endif

    {{ $logs->links() }}

    <div class="form">
        <form action="/admin/ipbans" method="post">
            @csrf
            <div class="form-inline">
                <div class="form-group{{ hasError('ip') }}">
                    <input type="text" class="form-control" id="ip" name="ip" maxlength="15" value="{{ getInput('ip') }}" placeholder="IP-address" required>
                </div>

                <button class="btn btn-primary">{{ __('main.add') }}</button>
            </div>
            <div class="invalid-feedback">{{ textError('ip') }}</div>
        </form>
    </div><br>

    <p class="text-muted font-italic">
        {!! __('admin.ipbans.hint') !!}
    </p>

    @if ($logs->isNotEmpty() && isAdmin('boss'))
        <i class="fa fa-times"></i> <a href="/admin/ipbans/clear?token={{ $_SESSION['token'] }}" onclick="return confirm('{{ __('admin.ipbans.confirm_clear') }}')">{{ __('main.clear') }}</a><br>
    @endif
@stop
