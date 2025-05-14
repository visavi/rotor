@extends('layout')

@section('title', __('index.search'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.search') }}</li>
        </ol>
    </nav>
@stop

@section('header')
    <div class="float-end">
        <form action="/admin/search/import" method="post">
            @csrf
            <button class="btn btn-success"><i class="fa-solid fa-file-import"></i> {{ __('main.import') }}</button>
        </form>
    </div>

    <h1>{{ __('index.search') }}</h1>
@stop

@section('content')
    @if ($search->isNotEmpty())
        <div class="mb-3">
            @foreach ($search as $data)
                <div class="mb-1">
                    <i class="fa fa-search"></i> <b>{{ $data->getRelateType() }}</b> ({{ $data->relate_type }}) <span class="badge bg-light text-dark">{{ __('main.total') }}: {{ $data->cnt }}</span>
                </div>
            @endforeach
        </div>

        <div class="mb-3">
            {{ __('main.total') }}: {{ $count }}
        </div>
    @else
        {{ showError(__('main.empty_records')) }}
    @endif
@stop
