@extends('layout')

@section('title', __('index.user_statuses'))

@section('header')
    <div class="float-right">
        <a class="btn btn-success" href="/admin/status/create">{{ __('main.create') }}</a>
    </div>

    <h1>{{ __('index.user_statuses') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.user_statuses') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($statuses->isNotEmpty())

        <div class="card">
            <h5 class="card-header">
                {{ __('statuses.list') }}
            </h5>

            <ul class="list-group list-group-flush">
                @foreach ($statuses as $status)
                    <li class="list-group-item">
                        <span{!! $status->color ? ' style="color:' . $status->color . '"' : '' !!}>
                            <i class="fa fa-user-circle"></i> <b>{{ $status->name }}</b>
                        </span>

                        <small>({{ $status->topoint }} - {{ $status->point }})</small>

                        <div class="float-right">
                            <a data-toggle="tooltip" title="{{ __('main.edit') }}" href="/admin/status/edit?id={{ $status->id }}"><i class="fa fa-pencil-alt text-muted"></i></a>
                            <a data-toggle="tooltip" title="{{ __('main.delete') }}" href="/admin/status/delete?id={{ $status->id }}&amp;token={{ $_SESSION['token'] }}" onclick="return confirm('{{ __('statuses.confirm_delete') }}')"><i class="fa fa-trash-alt text-muted"></i></a>
                        </div>
                    </li>
                @endforeach
            </ul>

            <div class="card-footer">
                {{ __('main.total') }}: <b>{{ $statuses->count() }}</b>
            </div>
        </div>
    @else
        {!! showError(__('statuses.empty_statuses')) !!}
    @endif
@stop
