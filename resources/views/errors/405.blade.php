@extends('layout')

@section('title')
    Ошибка 405
@stop

@section('content')

    <?php $images = glob(HOME.'/assets/img/errors/*.png'); ?>

    <div class="row">
        <div class="col-md-4 text-center">
            <img src="/assets/img/errors/{{ basename($images[array_rand($images)]) }}" alt="error 405">
        </div>
        <div class="col-md-8 text-center">
            <h1>Ошибка 405!</h1>

            <div class="lead">Переданный метод HTTP не поддерживается на данной странице!</div>

            @if ($referer)
                <div class="m-3">
                    <i class="fa fa-arrow-circle-left"></i> <a href="{{ $referer }}">Вернуться</a><br>
                </div>
            @endif
        </div>
    </div>
@stop
