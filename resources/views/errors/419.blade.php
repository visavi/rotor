@extends('layout')

@section('title', __('errors.error') . ' 419')

@section('header', '')
@section('description', __('errors.error') . ' 419')

@section('content')

    <?php $images = glob(HOME.'/assets/img/errors/*.png'); ?>

    <div class="row">
        <div class="col-12 text-center">
            <img src="/assets/img/errors/{{ basename($images[array_rand($images)]) }}" alt="error 419">
        </div>
        <div class="col-12 text-center">
            <h1>{{ __('errors.error') }} 419!</h1>

            <div class="lead">{{ __('errors.page_expired') }}</div>

            <div class="my-3">
                <a class="btn btn-primary" href="{{ url()->previous() }}"><i class="fa fa-arrow-circle-left"></i> {{ __('errors.return') }}</a>
            </div>
        </div>
    </div>
@stop
