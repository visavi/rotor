@extends(setting('themes') . '/theme')

@section('menu')
    @include('app/_menu')
@stop

@section('note')
    @include('app/_note')
@stop

@section('flash')
    @include('app/_flash')
@stop

@section('advertTop')
    @include('advert/_top_all')
@stop

@section('advertBottom')
    @include('advert/_bottom_all')
@stop

@section('styles')
    @include('app/_styles')
@stop

@section('scripts')
    @include('app/_scripts')
@stop

@section('advertUser')
    {!! getAdvertUser() !!}
@stop

@section('counter')
    {!! showCounter() !!}
@stop

@section('online')
    {!! showOnline() !!}
@stop

@section('performance')
    {!! performance() !!}
@stop

@section('header')
    <h1>@yield('title')</h1>
@stop
