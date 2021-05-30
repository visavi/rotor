@extends('layout')

@section('title', __('errors.error') . ' 403')

@section('header', '')
@section('description', __('errors.error') . ' 403')

@section('content')
    <?php $images = glob(public_path('assets/img/errors/*.png')); ?>

    <div class="row">
        <div class="col-12 text-center">
            <img src="/assets/img/errors/{{ basename($images[array_rand($images)]) }}" alt="error 403">
        </div>
        <div class="col-12 text-center">
            <h1>{{ __('errors.error') }} 403!</h1>

            <div class="lead">{{ $exception->getMessage() ?: __('errors.forbidden') }}</div>

            <div class="my-3">
                <a class="btn btn-primary" href="{{ url()->previous() }}"><i class="fa fa-arrow-circle-left"></i> {{ __('errors.return') }}</a>
            </div>
        </div>
    </div>
@stop
