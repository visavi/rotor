    <div class="small" id="down">
                        <?= showCounter() ?>
                        <?= showOnline() ?>
                        <?= perfomance() ?>
                    </div>
                </div>
            </div>

            <div id="footer">
                <div id="text">
                    &copy; Copyright 2005-<?=date('Y')?> {{ setting('title') }}
                </div>
                <div id="image">
                    <a href="/"><img src="/themes/motor/img/smalllogo2.gif" alt="smalllogo"></a>
                </div>
            </div>
            <img src="/themes/motor/img/panel_bot.gif" alt="">
        </div>
    </div>
</div>
@section('scripts')
    <?= includeScript() ?>
@show
@stack('scripts')
</body>
</html>
