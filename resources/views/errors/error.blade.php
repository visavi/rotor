{{-- Общий шаблон страниц ошибок. Код и текст приходят из файла конкретной ошибки:
     @extends('errors/error', ['code' => 404, 'message' => __('errors.not_found')])
     Ошибка до ApplyTheme (битый адрес, большой POST, сбой сессии) темы не имеет —
     layout упал бы на theme:: и превратил её в 500. Такие рисуются без оформления --}}
@extends(array_key_exists('theme', app('view')->getFinder()->getHints()) ? 'layout' : 'errors.minimal')

@section('title', trim(__('errors.error') . ' ' . ($code ?? '')))
@section('description', trim(__('errors.error') . ' ' . ($code ?? '')))
@section('header', '')

@section('content')
    <?php $images = glob(public_path('assets/img/errors/*.{gif,png,jpg,jpeg,webp}'), GLOB_BRACE); ?>

    <div class="text-center">
        <img src="/assets/img/errors/{{ basename($images[array_rand($images)]) }}" alt="error {{ $code ?? '' }}" class="img-fluid">

        <h1>{{ trim(__('errors.error') . ' ' . ($code ?? '')) }}!</h1>

        <div class="lead">{{ $exception->getMessage() ?: ($message ?? '') }}</div>

        <div class="my-3">
            @if (url()->previous() === url()->current())
                <a class="btn btn-primary" href="/"><i class="fa fa-arrow-circle-left"></i> {{ __('errors.to_main') }}</a>
            @else
                <a class="btn btn-primary" href="{{ url()->previous() }}"><i class="fa fa-arrow-circle-left"></i> {{ __('errors.return') }}</a>
            @endif
        </div>
    </div>
@stop
