@include(App::setting('themes').'.index' )

<div style="text-align:center">
    @include('advert.top_all')

    <?= show_advertuser(); /* Реклама за игровые деньги */ ?>
</div>

    {{ App::getFlash() }}

    @yield('content')

@include(App::setting('themes').'.foot')
