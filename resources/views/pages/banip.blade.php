@extends('layout_simple')

@section('title')
    {{ __('pages.banned') }}
@stop

@section('content')

    <h1>{{ __('pages.banned') }}</h1>

    {!! __('pages.banned_text') !!}<br>

    @if (! $ban->user_id)
        <form method="post">
            {!! view('app/_captcha') !!}
            <button class="btn btn-primary">{{ __('main.confirm') }}</button>
        </form>
    @endif
@stop
