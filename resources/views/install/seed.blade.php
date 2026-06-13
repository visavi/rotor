@extends('layout_simple')

@section('title', __('install.step3_install'))

@section('content')
    <div class="container border px-5">
        <div class="py-5 text-center">
            <a href="/"><img class="d-block mx-auto mb-3" src="/assets/img/images/logo_big.png" alt=""></a>
            <h2>Mobile CMS</h2>
        </div>

        <h1>{{ __('install.step3_install') }}</h1>

        <div class="alert alert-secondary">{!! nl2br(e(preg_replace("/\n{2,}/", "\n", trim($output)))) !!}</div>

        <a class="btn btn-primary" href="/install/account?lang={{ $lang }}">{{ __('install.create_admin') }}</a>

        <footer class="my-5 pt-5 text-muted text-center text-small">
            <p class="mb-1">&copy; 2005-{{ date('Y') }} VISAVI.NET</p>
        </footer>
    </div>
@stop
