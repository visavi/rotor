@extends('layout_simple')

@section('title', __('pages.banned'))

@section('content')
    <h1>{{ __('pages.banned') }}</h1>

    {!! __('pages.banned_text') !!}<br>

    @if (! $ban->user_id)
        @if ($ban->created_at < strtotime('-1 minute', SITETIME))
            <div class="section-form p-3 shadow">
                <form method="post">
                    {!! view('app/_captcha') !!}
                    <button class="btn btn-primary">{{ __('main.confirm') }}</button>
                </form>
            </div>
        @else
            {!! __('pages.banned_wait') !!}

            <div class="mt-3">
                <button class="btn btn-secondary" disabled>{{ __('main.confirm') }}</button>
            </div>
        @endif
    @endif
@stop
