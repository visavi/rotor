@extends('layout_simple')

@section('title')
    {{ trans('pages.banned') }}
@stop

@section('content')

    <h1>{{ trans('pages.banned') }}</h1>

    {!! trans('pages.banned_text') !!}<br>

    @if (! $ban->user_id)
        <form method="post">
            {!! view('app/_captcha') !!}
            <button class="btn btn-primary">{{ trans('main.confirm') }}</button>
        </form>
    @endif
@stop
