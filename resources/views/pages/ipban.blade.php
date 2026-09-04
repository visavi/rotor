@extends('layout_simple')

@section('title', __('pages.banned'))

@section('content')
    <div class="container my-4" style="max-width: 720px;">
        <h1 class="h3 mb-3">{{ __('pages.banned') }}</h1>

        <div class="section mb-3 shadow">
            <div class="section-body">
                {!! __('pages.banned_text') !!}
            </div>
        </div>

        @if (! $ban->user_id)
            <div class="section-form mb-3 shadow">
                @if ($ban->created_at->lt(now()->subMinute()))
                    <form method="post">
                        {{ getCaptcha() }}
                        <button class="btn btn-primary">{{ __('main.confirm') }}</button>
                    </form>
                @else
                    <div class="mb-3">{!! __('pages.banned_wait') !!}</div>

                    <button class="btn btn-secondary" disabled>{{ __('main.confirm') }}</button>
                @endif
            </div>
        @endif
    </div>
@stop
