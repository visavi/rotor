<?php include_once(BASEDIR.'/themes/header.php'); ?>

<div class="col-lg-12">
    {{ App::getFlash() }}
</div>

    @yield('content')
<?php /*include_once(BASEDIR.'/themes/footer.php');*/ ?>
