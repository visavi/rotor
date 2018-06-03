@if (getUser())
    <i class="fa fa-user-circle fa-lg"></i> <a href="/users/{{ getUser('login') }}">{{ getUser('login') }}</a> &bull;

    @if (isAdmin())
        <a href="/admin">Панель</a> &bull;
        @if (statsSpam() > 0)
            <a href="/admin/spam"><span style="color:#ff0000">Жалобы</span></a> &bull;
        @endif

        @if (getUser('newchat') < statsNewChat())
            <a href="/admin/chats"><span style="color:#ff0000">Чат</span></a> &bull;
        @endif
    @endif
        <a href="/menu">Меню</a> &bull;
        <a href="/logout?token={{ $_SESSION['token'] }}" onclick="return logout(this)">Выход</a>
@else
    <i class="fa fa-lock fa-lg"></i> <a href="/login<?= returnUrl() ?>" rel="nofollow">Авторизация</a> &bull;
    <a href="/register" rel="nofollow">Регистрация</a>
@endif
