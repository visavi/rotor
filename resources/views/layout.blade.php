@extends('theme::layout')

@section('header')
    <h1>@yield('title')</h1>
@stop

@section('flash')
    @include('app/_flash')
@stop

@section('titlebar')
    <div class="app-title">
        @yield('header')
        @yield('breadcrumb')
        @hook('header')
    </div>
@stop

@section('navbar')
    @includeIf('theme::navbar')
@stop

@section('sidebar')
    @includeIf('theme::sidebar')
@stop

@push('scripts')
    @include('app/_language_modal')
    @includeWhen(getUser(), 'app/_comment_edit_modal')
@endpush
