@include(setting('themes').'.index' )

<div style="text-align:center">
    @include('advert.top_all')

    <?= showAdvertUser(); ?>
</div>

{{ getFlash() }}

@yield('content')

@include('advert.bottom_all')
@include(setting('themes').'.foot')
