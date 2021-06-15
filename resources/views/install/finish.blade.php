@extends('layout_simple')

@section('title', __('install.install_completed'))

@section('content')
    <div class="container border bg-white px-5">
        <div class="py-5 text-center">
            <a href="/"><img class="d-block mx-auto mb-3" src="/assets/img/images/logo_big.png" alt=""></a>
            <h2>Mobile CMS</h2>
        </div>

        <h1>{{ __('install.install_completed') }}</h1>
        <div>
            <div class="alert alert-success">
                {{ __('install.success_install') }}
            </div>

            <a class="btn btn-primary" href="/">{{ __('install.main_page') }}</a><br>
        </div>

        <footer class="my-5 pt-5 text-muted text-center text-small">
            <p class="mb-1">&copy; 2005-{{ date('Y') }} VISAVI.NET</p>
        </footer>
    </div>
@stop
