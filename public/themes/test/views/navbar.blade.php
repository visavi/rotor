<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
</button>

<div class="collapse navbar-collapse" id="navbarResponsive">
    <ul class="navbar-nav ml-auto">
        @if ($user = getUser())
            @if (isAdmin())
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="dropdown01" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-cog"></i></a>
                    <div class="dropdown-menu" aria-labelledby="dropdown01">
                        <a class="dropdown-item" href="/admin">Панель</a>
                        <a class="dropdown-item{{ statsSpam() ? ' text-danger' : '' }}" href="/admin/spam">Чат</a>
                        <a class="dropdown-item{{ $user->newchat < statsNewChat() ? ' text-danger' : '' }}" href="/admin/chats">Жалобы</a>
                    </div>
                </li>
            @endif

            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="dropdown02" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{!! $user->getAvatarImage() !!}</a>
                <div class="dropdown-menu" aria-labelledby="dropdown02">
                    <a class="dropdown-item" href="/users/{{ $user->login }}">{{ $user->getName() }}</a>
                    <a class="dropdown-item" href="/logout?token={{ $_SESSION['token'] }}" onclick="return logout(this)">Выход</a>
                </div>
            </li>
        @else
            <li class="nav-item">
                <a class="nav-link" href="/login{{ returnUrl() }}" rel="nofollow">Авторизация</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="register" rel="nofollow">Регистрация</a>
            </li>
        @endif
    </ul>
</div>
