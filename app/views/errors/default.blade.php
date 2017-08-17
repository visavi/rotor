@extends('layout')

@section('title')
    Ошибка - @parent
@stop

@section('content')

    <?php $images = glob(HOME.'/assets/img/errors/*.png'); ?>

    <div class="row">
        <div class="col-md-4 text-center">
            <img src="/assets/img/errors/{{ basename($images[array_rand($images)]) }}" alt="error">
        </div>
        <div class="col-md-8">
            <h3>{{ $message }}</h3>
        </div>
    </div>
@stop
