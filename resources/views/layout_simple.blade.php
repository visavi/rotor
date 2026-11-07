{{-- Страница без обвязки: шапка, меню, футер и реклама гасятся, но оформление берётся
     из макета активной темы — свои стили тема подключает сама --}}
@extends('theme::layout')

@section('navbar')@stop
@section('sidebar')@stop
@section('titlebar')@stop
@section('footer')@stop
@section('advertTop')@stop
@section('advertBottom')@stop

@section('flash')
    @include('app/_flash')
@stop
