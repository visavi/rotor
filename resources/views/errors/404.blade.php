@extends('layout')

@section('title')
    Ошибка 404
@stop

@section('content')

    <?php $images = glob(HOME.'/assets/img/errors/*.png'); ?>

    <div class="row">
        <div class="col-md-4 text-center">
            <img src="/assets/img/errors/{{ basename($images[array_rand($images)]) }}" alt="error 404">
        </div>
        <div class="col-md-8 text-center">
            <h1>Ошибка 404!</h1>

            @if ($message)
                <div class="lead">{{ $message }}</div>
            @else
                <div class="lead">Данной страницы не существует!</div>
            @endif

            @if ($referer)
                <div class="m-3">
                    <i class="fa fa-arrow-circle-left"></i> <a href="{{ $referer }}">Вернуться</a><br>
                </div>
            @endif
        </div>
    </div>
@stop
