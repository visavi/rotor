@extends('layout')

@section('title')
    {{ trans('index.errors') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('index.errors') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if (empty(setting('errorlog')))
        <span class="text-danger">{{ trans('admin.errors.hint') }}</span><br>
    @endif

    <ol class="breadcrumb">
        @foreach ($lists as $key => $value)
            <li class="breadcrumb-item">
                @if ($key === $code)
                    <b>{{ $value }}</b>
                @else
                    <a href="/admin/errors?code={{ $key }}">{{ $value }}</a>
                @endif
            </li>
        @endforeach
    </ol>

    @if ($logs->isNotEmpty())

        @foreach ($logs as $data)
            <div class="b">
                <i class="fa fa-file"></i>
                <b>{{ $data->request }}</b> ({{ dateFixed($data->created_at) }})
            </div>
            <div>
                Referer: {{ $data->referer ?: trans('main.undefined') }}<br>
                {{ trans('main.user') }}: {!! $data->user->getProfile() !!}<br>
                <span class="data">({{ $data->brow }}, {{ $data->ip }})</span>
            </div>
        @endforeach

        {!! pagination($page) !!}

        {{ trans('main.total') }}: <b>{{ $page->total }}</b><br><br>

        @if (isAdmin('boss'))
            <i class="fa fa-trash-alt"></i> <a href="/admin/errors/clear?token={{ $_SESSION['token'] }}">{{ trans('main.clear') }}</a><br>
        @endif

    @else
        {!! showError(trans('main.empty_records')) !!}
    @endif
@stop
