@extends('layout')

@section('title')
    Ошибка 403 - @parent
@stop

@section('content')

    <?php $images = glob(HOME.'/assets/img/errors/*.png'); ?>

    <div class="row">
        <div class="col-md-4 text-center">
            <img src="/assets/img/errors/{{ basename($images[array_rand($images)]) }}" alt="error 403" />
        </div>
        <div class="col-md-8">
            <h3>Ошибка 403!</h3>
            <div class="lead">Доступ запрещен!</div>

            @if ($message)
                <div class="lead">{{ $message }}</div>
            @endif
        </div>
    </div>
@stop
