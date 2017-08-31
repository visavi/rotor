</div>
<div class="lol" id="down">
    <a href="/">{{ setting('copy') }}</a><br>
    <?= showOnline() ?>
    <?= showCounter() ?>
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
