{{-- Разделы сайта закрыты, поэтому меню и футер не показываем --}}
@extends('layout_simple')

@section('title', __('pages.closed'))

@section('content')
    <?php $images = glob(public_path('assets/img/errors/*.png')); ?>

    <div class="container my-4 text-center" style="max-width: 720px;">
        <img src="/assets/img/errors/{{ basename($images[array_rand($images)]) }}" alt="error" class="img-fluid">

        <h1 class="h3 mt-3">{{ __('pages.closed_text1') }}</h1>

        <div class="lead">{{ __('pages.closed_text2') }}</div>

        <a class="btn btn-primary mt-3" href="{{ route('login') }}">
            <i class="fas fa-right-to-bracket"></i> {{ __('index.login') }}
        </a>
    </div>
@stop
