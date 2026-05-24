@use('App\Classes\Hook')
@extends('theme::layout')

@section('header')
    <h1>@yield('title')</h1>
@stop

@section('flash')
    @include('app/_flash')
@stop

@section('advertTop')
    {!! Hook::call('advertTop') !!}
@stop

@section('advertBottom')
    {!! Hook::call('advertBottom') !!}
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

@if (getUser())
    @push('scripts')
        @include('app/_comment_edit_modal')
    @endpush
@endif
