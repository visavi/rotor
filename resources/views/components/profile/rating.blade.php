@props(['rating', 'positive', 'negative', 'url', 'plusUrl' => null, 'minusUrl' => null])

@php
    // Доля плюсов: один и тот же итог бывает и у 745/114, и у 631/0 — это разные репутации
    $share = (int) round($positive / max($positive + $negative, 1) * 100);
@endphp

<div class="profile-rating">
    <div class="profile-rating-head">
        <b class="profile-rating-total {{ $rating >= 0 ? 'text-success' : 'text-danger' }}">
            {{ $rating > 0 ? '+' : '' }}{{ $rating }}
        </b>

        <span class="text-muted">{{ __('main.positive_share', ['share' => $share]) }}</span>

        @if ($plusUrl && $minusUrl)
            <span class="profile-rating-vote ms-auto">
                <a class="btn btn-sm btn-outline-success" href="{{ $plusUrl }}"><i class="fa fa-arrow-up"></i> {{ __('main.plus') }}</a>
                <a class="btn btn-sm btn-outline-danger" href="{{ $minusUrl }}"><i class="fa fa-arrow-down"></i> {{ __('main.minus') }}</a>
            </span>
        @endif
    </div>

    <div class="profile-rating-bar">
        <span style="width: {{ $share }}%"></span>
    </div>

    <div class="profile-rating-legend">
        <a class="text-success" href="{{ $url }}"><i class="fa fa-arrow-up"></i> {{ $positive }}</a>
        <a class="text-danger" href="{{ $url }}"><i class="fa fa-arrow-down"></i> {{ $negative }}</a>
        <a class="ms-auto" href="{{ $url }}">{{ __('main.who_voted') }}</a>
    </div>
</div>
