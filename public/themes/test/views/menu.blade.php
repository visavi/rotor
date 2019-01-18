<div class="collapse navbar-collapse" id="navbarResponsive">
    <ul class="navbar-nav ml-auto">
        @if ($user = getUser())
            <li class="nav-item">
                <a class="nav-link tools-item" href="/messages">
                    <i class="fas fa-bell"></i>
                    @if ($user->newprivat)
                        <i class="tools-item-count">{{ $user->newprivat }}</i>
                    @endif
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link tools-item" href="/walls/{{ getUser('login') }}">
                    <i class="fas fa-comment-alt"></i>
                    @if ($user->newwall)
                        <i class="tools-item-count">{{ $user->newwall }}</i>
                    @endif
                </a>
            </li>

            @if (isAdmin())
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="dropdown01" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-wrench"></i></a>
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
