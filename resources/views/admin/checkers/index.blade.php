@extends('layout')

@section('title', __('index.site_scan'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.site_scan') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($diff)
        <h5>{{ __('admin.checkers.new_files') }}:</h5>

        <div class="section-message">
            @if ($diff['left'])
                @foreach ($diff['left'] as $file)
                    <i class="fa fa-plus-circle text-success"></i> {{ $file }}<br>
                @endforeach
                <br>
            @else
                {{ showError(__('admin.checkers.empty_changes')) }}
            @endif
        </div>

        <h5>{{ __('admin.checkers.old_files') }}:</h5>

        <div class="section-message">
            @if ($diff['right'])
                @foreach ($diff['right'] as $file)
                    <i class="fa fa-minus-circle text-danger"></i> {{ $file }}<br>
                @endforeach
                <br>
            @else
                {{ showError(__('admin.checkers.empty_changes')) }}
            @endif
        </div>
    @else
        {{ showError(__('admin.checkers.initial_scan')) }}
    @endif

    <p class="text-muted fst-italic">
        {{ __('admin.checkers.information_scan') }}<br>
        {{ __('admin.checkers.invalid_extensions') }}: {{ setting('nocheck') }}
    </p>

    <p><a class="btn btn-primary" href="/admin/checkers/scan?token={{ csrf_token() }}"><i class="fa fa-sync"></i> {{ __('admin.checkers.scan') }}</a></p>
@stop
