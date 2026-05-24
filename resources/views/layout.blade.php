@extends('theme::layout')

@section('header')
    <h1>@yield('title')</h1>
@stop

@section('flash')
    @include('app/_flash')
@stop

@if (getUser())
    @push('scripts')
        @include('app/_comment_edit_modal')
    @endpush
@endif
