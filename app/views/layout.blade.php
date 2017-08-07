@include(Setting::get('themes').'.index' )

<div style="text-align:center">
    @include('advert.top_all')

    <?= show_advertuser(); ?>
</div>

{{ App::getFlash() }}

@yield('content')

@include('advert.bottom_all')
@include(Setting::get('themes').'.foot')
