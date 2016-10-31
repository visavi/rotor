@include(App::setting('themes').'.index' )

<div style="text-align:center">
    <?php include_once (STORAGE.'/advert/top_all.dat'); ?>

    <?= show_advertuser(); /* Реклама за игровые деньги */ ?>
</div>

    {{ App::getFlash() }}

    @yield('content')

@include(App::setting('themes').'.foot')
