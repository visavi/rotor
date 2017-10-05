@extends('layout')

@section('title')
    Ошибка
@stop

@section('content')

    <?php $images = glob(HOME.'/assets/img/errors/*.png'); ?>

    <div class="row">
        <div class="col-md-4 text-center">
            <img src="/assets/img/errors/{{ basename($images[array_rand($images)]) }}" alt="error">
        </div>
        <div class="col-md-8">
            <h3>{{ $message }}</h3>

            @if ($referer)
                <div style="position: absolute; bottom: 0;">
                    <i class="fa fa-arrow-circle-left"></i> <a href="{{ $referer }}">Вернуться</a><br>
                </div>
            @endif
        </div>
    </div>
@stop
