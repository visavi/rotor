@extends('layout')

@section('title', 'Ошибка 403 - @parent')

@section('content')

    <?php $images = glob(BASEDIR.'/images/errors/*.png'); ?>

    <div class="row">
        <div class="col-md-4 text-right">
            <img src="/images/errors/{{ basename($images[array_rand($images)]) }}" alt="error 403" />
        </div>
        <div class="col-md-6">
            <h3>Ошибка 403!</h3>
            <div class="lead">Доступ запрещен!</div>
        </div>
    </div>
@stop
