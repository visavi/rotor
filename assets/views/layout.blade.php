@include(App::setting('themes').'.index' )

<div style="text-align:center">
    <?php include_once (DATADIR.'/advert/top_all.dat'); ?>

    <?= show_advertadmin(); /* Админска реклама */ ?>
    <?= show_advertuser(); /* Реклама за игровые деньги */ ?>
</div>

<?php render('includes/note'); ?>

    {{ App::getFlash() }}

    @yield('content')

@include(App::setting('themes').'.foot')
