@extends(setting('themes').'/template')

@section('layout')
    @include('advert/top_all')
    {{ getAdvertUser() }}
    {{ getNote() }}
    {{ getFlash() }}

    @yield('content')

    @include('advert/bottom_all')
@stop
