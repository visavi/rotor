@extends('layout')

@section('title')
    {{ trans('index.ip_ban') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('index.ip_ban') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <a href="/admin/errors?code=666">{{ trans('admin.ipbans.history') }}</a><br>

    @if ($logs->isNotEmpty())

        <form action="/admin/ipbans/delete?page={{ $page->current }}" method="post">
            @csrf
            @foreach ($logs as $log)
                <div class="b">
                    <input type="checkbox" name="del[]" value="{{ $log->id }}">
                    <i class="fa fa-file"></i> <b>{{ $log->ip }}</b>
                </div>

                <div>{{ trans('main.added') }}:
                    @if ($log->user->id)
                        <b>{!! $log->user->getProfile() !!}</b>
                    @else
                        <b>{{ trans('main.automatically') }}</b>
                    @endif

                    ({{ dateFixed($log->created_at) }})
                </div>
            @endforeach

            <button class="btn btn-sm btn-danger">{{ trans('main.delete_selected') }}</button>
        </form>

        {!! pagination($page) !!}

        {{ trans('main.total') }}: <b>{{ $page->total }}</b><br><br>
    @else
        {!! showError(trans('admin.ipbans.empty_ip')) !!}
    @endif

    <div class="form">
        <form action="/admin/ipbans" method="post">
            @csrf
            <div class="form-inline">
                <div class="form-group{{ hasError('ip') }}">
                    <input type="text" class="form-control" id="ip" name="ip" maxlength="15" value="{{ getInput('ip') }}" placeholder="IP-address" required>
                </div>

                <button class="btn btn-primary">{{ trans('main.add') }}</button>
            </div>
            <div class="invalid-feedback">{{ textError('ip') }}</div>
        </form>
    </div><br>

    <p class="text-muted font-italic">
        {!! trans('admin.ipbans.hint') !!}
    </p>

    @if ($logs->isNotEmpty() && isAdmin('boss'))
        <i class="fa fa-times"></i> <a href="/admin/ipbans/clear?token={{ $_SESSION['token'] }}" onclick="return confirm('{{ trans('admin.ipbans.confirm_clear') }}')">{{ trans('main.clear') }}</a><br>
    @endif
@stop
