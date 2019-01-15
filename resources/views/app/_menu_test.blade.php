<ul class="navbar-nav ml-auto">

    @if ($user = getUser())

        <li class="nav-item">
             <a class="nav-link" href="/users/{{ $user->login }}"><i class="fa fa-user-circle"></i> {{ $user->getName() }}</a>
        </li>

        @if (isAdmin())
            <li class="nav-item">
                <a class="nav-link" href="/admin"><i class="fa fa-cog"></i> Панель</a>
            </li>
            @if (statsSpam() > 0)
                <li class="nav-item">
                    <a class="nav-link text-danger" href="/admin/spam"><i class="fa fa-exclamation"></i> Жалобы</a>
                </li>
            @endif
            @if ($user->newchat < statsNewChat())
                <li class="nav-item">
                    <a class="nav-link text-danger" href="/admin/chats"><i class="fa fa-cloud"></i> Чат</a>
                </li>
            @endif
        @endif

        <li class="nav-item">
            <a class="nav-link" href="/menu"><i class="fa fa-list"></i> Меню</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="/logout?token={{ $_SESSION['token'] }}" onclick="return logout(this)"><i class="fa fa-times"></i> Выход</a>
        </li>
    @else
        <li class="nav-item">
            <a class="nav-link" href="/login{{ returnUrl() }}" rel="nofollow"><i class="fa fa-lock"></i> Авторизация</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="register" rel="nofollow"><i class="fa fa-lock"></i> Регистрация</a>
        </li>
    @endif
</ul>
