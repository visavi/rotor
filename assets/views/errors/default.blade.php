@extends('layout')

@section('title', 'Ошибка - @parent')

@section('content')

    <?php $images = glob(BASEDIR.'/images/errors/*.png'); ?>

    <div class="row">
        <div class="col-md-4 text-right">
            <img src="/images/errors/{{ basename($images[array_rand($images)]) }}" alt="error" />
        </div>
        <div class="col-md-6">
            <h3>Ошибка!</h3>
            <div class="lead">{{ $message }}</div>
        </div>
    </div>
@stop
