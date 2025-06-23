@extends('layout')

@section('title', setting('logos'))

@section('header')
    <h1>{{ setting('title') }}</h1>
    <p>{{ setting('logos') }}</p>
@stop

@section('content')
    @include('ads/_top')

    @auth
        Авторизован
    @endauth

    @guest
        Не авторизован
    @endguest

    <?php
    //request()->session()->forget(['login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d']);


    dump(/*session()->flush(),*/ request()->cookies->all(), request()->session()->all(), getUser('login'), request()->user()); ?>

    @if (setting('homepage_view') === 'feed')
        {{ (new \App\Classes\Feed())->getFeed() }}
    @else
        @include('widgets/_classic')
    @endif

    @include('ads/_bottom')
@stop
