@extends('layout')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4 section shadow settings-nav-col">
                @include('admin/settings/_nav')
            </div>
            <div class="col-md-8 section shadow">
                @yield('settings')
            </div>
        </div>
    </div>
@stop

@push('scripts')
    <script type="module">
        const current = location.pathname + location.search;

        document.querySelectorAll('.js-settings-nav .nav-link').forEach((link) => {
            const url = new URL(link.href, location.origin);

            if (url.pathname + url.search === current) {
                link.classList.add('active');
            }
        });
    </script>
@endpush
