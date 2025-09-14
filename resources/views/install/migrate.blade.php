@extends('layout_simple')

@section('title', setting('app_installed') ? __('install.update_completed') : __('install.step3_install'))

@section('content')
    <div class="container border px-5">
        <div class="py-5 text-center">
            <a href="/"><img class="d-block mx-auto mb-3" src="/assets/img/images/logo_big.png" alt=""></a>
            <h2>Mobile CMS</h2>
        </div>

        <h1>{{ setting('app_installed') ? __('install.update_completed') : __('install.step3_install') }}</h1>

        <pre class="prettyprint p-3 mb-3">{{ $output }}</pre>


        @if (setting('app_installed'))
            <div>
                <div class="alert alert-success">
                    {{ __('install.success_update') }}
                </div>

                <a class="btn btn-primary" href="/">{{ __('install.main_page') }}</a><br>
            </div>
        @else
            <div>
                <a class="btn btn-primary" href="/install/seed?lang={{ $lang }}">{{ __('install.seeds') }}</a>
            </div>
        @endif

        <footer class="my-5 pt-5 text-muted text-center text-small">
            <p class="mb-1">&copy; 2005-{{ date('Y') }} VISAVI.NET</p>
        </footer>
    </div>
@stop
