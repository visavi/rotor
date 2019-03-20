@extends('layout')

@section('title')
    {{ trans('errors.error') }} 405
@stop

@section('header', '')
@section('description', trans('errors.error') . ' 405')
@section('keywords', '')

@section('content')

    <?php $images = glob(HOME.'/assets/img/errors/*.png'); ?>

    <div class="row">
        <div class="col-md-4 text-center">
            <img src="/assets/img/errors/{{ basename($images[array_rand($images)]) }}" alt="error 405">
        </div>
        <div class="col-md-8 text-center">
            <h1>{{ trans('errors.error') }} 405!</h1>

            <div class="lead">{{ trans('errors.method_not_allowed') }}</div>

            @if ($referer)
                <div class="m-3">
                    <i class="fa fa-arrow-circle-left"></i> <a href="{{ $referer }}">{{ trans('errors.return') }}</a><br>
                </div>
            @endif
        </div>
    </div>
@stop
