</div>
<div class="lol" id="down">
    <a href="/">{{ App::setting('copy') }}</a><br />
    <?= show_online() ?>
    <?= show_counter() ?>
</div>
<div class="site" style="text-align:center">
    <?= perfomance() ?>
</div>

@section('scripts')
    <?= include_javascript() ?>
@show
@stack('scripts')
</body>
</html>
