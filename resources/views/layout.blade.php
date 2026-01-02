@extends('theme::layout')

@section('header')
    <h1>@yield('title')</h1>
@stop

@section('flash')
    @include('app/_flash')
@stop

@section('advertTop')
    @include('ads/_top_all')
@stop

@section('advertBottom')
    @include('ads/_bottom_all')
@stop

@section('advertAdmin')
    {{ getAdvertAdmin() }}
@stop

@section('advertUser')
    {{ getAdvertUser() }}
@stop

@section('counter')
    {{ showCounter() }}
@stop

@section('online')
    {{ showOnline() }}
@stop

@section('performance')
    {{ performance() }}
@stop
