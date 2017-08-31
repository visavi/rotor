<div class="menu">
    @if (is_user())
        {!! user_gender(user()) !!}
        {!! profile(user()) !!} &bull;

        @if (is_admin())
            <a href="/admin">Панель</a> &bull;
            @if (stats_spam()>0)
                <a href="/admin/spam"><span style="color:#ff0000">Спам!</span></a> &bull;
            @endif

            @if (user('newchat')<stats_newchat())
                <a href="/admin/chat"><span style="color:#ff0000">Чат</span></a> &bull;
            @endif
        @endif
            <a href="/menu">Меню</a> &bull;
            <a href="/logout" onclick="return logout(this)">Выход</a>
    @else
        <i class="fa fa-lock fa-lg"></i> <a href="/login<?= returnUrl() ?>" rel="nofollow">Авторизация</a> &bull;
        <a href="/register" rel="nofollow">Регистрация</a>
    @endif
</div>
