@extends('layout')

@section('title', __('pages.closed'))

@section('content')
    <?php $images = glob(public_path('assets/img/errors/*.png')); ?>

    <div class="row">
        <div class="col-12 text-center">
            <img src="/assets/img/errors/{{ basename($images[array_rand($images)]) }}" alt="error">
        </div>
        <div class="col-12 text-center">
            <h1>{{ __('pages.closed_text1') }}</h1>

            <div class="lead">{{ __('pages.closed_text2') }}</div>
        </div>
    </div>
@stop
