    <div class="small" id="down">
                        <?= show_counter() ?>
                        <?= show_online() ?>
                        <?= perfomance() ?>
                    </div>
                </div>
            </div>

            <div id="footer">
                <div id="text">
                    &copy; Copyright 2005-<?=date('Y')?> {{ App::setting('title') }}
                </div>
                <div id="image">
                    <a href="/"><img src="/themes/motor/img/smalllogo2.gif" alt="smalllogo" /></a>
                </div>
            </div>
            <img src="/themes/motor/img/panel_bot.gif" alt="" />
        </div>
    </div>
</div>
@section('scripts')
    <?= include_javascript() ?>
@show
@stack('scripts')
</body>
</html>
