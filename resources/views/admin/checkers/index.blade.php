@extends('layout')

@section('title')
    {{ trans('index.site_scan') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('index.site_scan') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($diff)
        <b><span style="color:#ff0000">{{ trans('admin.checkers.new_files') }}:</span></b><br><br>

        @if ($diff['left'])
            @foreach($diff['left'] as $file)
                <i class="fa fa-plus-circle text-success"></i> {{ $file }}<br>
            @endforeach
            <br>
        @else
            {!! showError(trans('admin.checkers.empty_changes')) !!}
        @endif

        <b><span style="color:#ff0000">{{ trans('admin.checkers.old_files') }}:</span></b><br><br>

        @if ($diff['right'])
            @foreach($diff['right'] as $file)
                <i class="fa fa-minus-circle text-danger"></i> {{ $file }}<br>
            @endforeach
            <br>
        @else
            {!! showError(trans('admin.checkers.empty_changes')) !!}
        @endif

    @else
        {!! showError(trans('admin.checkers.initial_scan')) !!}
    @endif

    <p class="text-muted font-italic">
        {{ trans('admin.checkers.information_scan') }}<br>
        {{ trans('admin.checkers.invalid_extensions') }}: {{ setting('nocheck') }}
    </p>

    <p><a class="btn btn-primary" href="/admin/checkers/scan?token={{ $_SESSION['token'] }}"><i class="fa fa-sync"></i> {{ trans('admin.checkers.scan') }}</a></p>
@stop
