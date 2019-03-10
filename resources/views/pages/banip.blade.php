@extends('layout_simple')

@section('title')
    {{ trans('pages.banned') }}
@stop

@section('content')

    <h1>{{ trans('pages.banned') }}</h1>

    <b>{{ trans('pages.possible_reasons') }}:</b><br>
    1. {{ trans('pages.banned_text1') }}<br>
    2. {{ trans('pages.banned_text2') }}<br>
    3. {{ trans('pages.banned_text3') }}<br><br>

    <b>{{ trans('pages.banned_text4') }}</b><br>
    {{ trans('pages.banned_text5') }}<br>
    {{ trans('pages.banned_text6') }}<br><br>

    {{ trans('pages.banned_text7') }}<br>
    {{ trans('pages.banned_text8') }}<br>
    {{ trans('pages.banned_text9') }}<br>
    {{ trans('pages.banned_text10') }}<br>
    <br>

    @if (! $ban->user_id)
        <form method="post">
            {!! view('app/_captcha') !!}
            <button class="btn btn-primary">{{ trans('main.confirm') }}</button>
        </form>
    @endif
@stop
