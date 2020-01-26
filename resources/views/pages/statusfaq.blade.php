@extends('layout')

@section('title')
    {{ __('index.user_statuses') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ __('index.user_statuses') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    {{ __('statuses.status_text1') }}<br>
    {{ __('statuses.status_text2') }}<br>
    {{ __('statuses.status_text3') }}<br><br>

    @if ($statuses->isNotEmpty())
        @foreach ($statuses as $status)

            <i class="fa fa-user-circle"></i>

            @if ($status->color)
                <b><span style="color:{{ $status->color }}">{{ $status->name }}</span></b> — {{ plural($status->topoint, setting('scorename')) }}<br>
            @else
                <b>{{ $status->name }}</b> — {{ plural($status->topoint, setting('scorename')) }}<br>
            @endif
        @endforeach

        <br>
    @else
        {!! showError(__('statuses.empty_statuses')) !!}
    @endif

    {{ __('statuses.status_text4') }}<br>
    {{ __('statuses.status_text5') }}<br><br>
@stop
