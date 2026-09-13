@extends('layout')

@section('title', __('index.user_statuses'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ __('index.user_statuses') }}</li>
        </ol>
    </nav>
@stop

@php
$point = getUser('point') ?? 0;
// Статус определяется диапазоном topoint..point — так же, как в User::getStatuses()
$current = getUser() ? $statuses->first(fn ($status) => $point >= $status->topoint && $point <= $status->point) : null;
$next = getUser() ? $statuses->last(fn ($status) => $status->topoint > $point) : null;
@endphp

@section('content')
    <div class="section mb-3 shadow">
        <div class="section-body text-muted">
            {{ __('statuses.status_text1') }}<br>
            {{ __('statuses.status_text2') }}<br>
            {{ __('statuses.status_text3') }}
        </div>
    </div>

    @if ($next)
        @php
        $from = $current->topoint ?? 0;
        $percent = min(100, (int) round(($point - $from) / max(1, $next->topoint - $from) * 100));
        @endphp

        <div class="section mb-3 shadow">
            <div class="section-body">
                <div class="d-flex justify-content-between mb-1">
                    <span>{{ __('statuses.next_status') }}: <b @style(['color: ' . $next->color => $next->color])>{{ $next->name }}</b></span>
                    <span class="text-muted">{{ __('statuses.your_points') }}: {{ plural($point, setting('scorename')) }}</span>
                </div>

                <div class="progress" style="height: .5rem">
                    <div class="progress-bar" style="width: {{ $percent }}%"></div>
                </div>

                <div class="text-muted small mt-1">
                    {{ __('statuses.points_left') }}: {{ plural($next->topoint - $point, setting('scorename')) }}
                </div>
            </div>
        </div>
    @endif

    @if ($statuses->isNotEmpty())
        <div class="status-list mb-3">
            @foreach ($statuses as $status)
                <div class="status-row{{ $current && $current->is($status) ? ' is-current' : '' }}">
                    <i class="fas fa-award status-row-icon" @style(['color: ' . $status->color => $status->color])></i>

                    <span class="status-row-name" @style(['color: ' . $status->color => $status->color])>{{ $status->name }}</span>

                    @if ($current && $current->is($status))
                        <span class="badge bg-primary">{{ __('statuses.your_status') }}</span>
                    @endif

                    <span class="status-row-point">{{ plural($status->topoint, setting('scorename')) }}</span>
                </div>
            @endforeach
        </div>
    @else
        {{ showError(__('statuses.empty_statuses')) }}
    @endif

    <div class="text-muted">
        {{ __('statuses.status_text4') }}<br>
        {{ __('statuses.status_text5') }}
    </div>
@stop
