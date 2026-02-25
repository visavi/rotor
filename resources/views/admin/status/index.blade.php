@extends('layout')

@section('title', __('index.user_statuses'))

@section('header')
    <div class="float-end">
        <a class="btn btn-success" href="/admin/status/create">{{ __('main.create') }}</a>
    </div>

    <h1>{{ __('index.user_statuses') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('index.panel') }}</a></li>
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
                        <span style="color: {{ $status->color ? $status->color : 'inherit' }}">
                            <i class="fa fa-user-circle"></i> <b>{{ $status->name }}</b>
                        </span>

                        <small>({{ $status->topoint }} - {{ $status->point }})</small>

                        <div class="float-end">
                            <a data-bs-toggle="tooltip" title="{{ __('main.edit') }}" href="/admin/status/edit?id={{ $status->id }}"><i class="fa fa-pencil-alt text-muted"></i></a>

                            <form action="/admin/status/delete" method="post" class="d-inline" onsubmit="return confirm('{{ __('statuses.confirm_delete') }}')">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="id" value="{{ $status->id }}">
                                <button class="btn btn-link p-0" data-bs-toggle="tooltip" title="{{ __('main.delete') }}"><i class="fa fa-trash-alt text-muted"></i></button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>

            <div class="card-footer">
                {{ __('main.total') }}: <b>{{ $statuses->count() }}</b>
            </div>
        </div>
    @else
        {{ showError(__('statuses.empty_statuses')) }}
    @endif
@stop
