<nav class="navbar navbar-expand navbar-light bg-white">
    <div class="navbar-brand">
        <button type="button" class="btn btn-sidebar" data-toggle="sidebar">
            <i class="fas fa-bars"></i>
        </button>
        <a href="/"><span class="pr-2">{{ setting('title') }}</span></a>
    </div>
    <ul class="navbar-nav ml-auto">
        <li class="nav-item dropdown">
            <a href="#" class="nav-link dropdown-toggle" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="badge badge-pill badge-primary">0</span> <i class="far fa-bell"></i></a>

            <div class="dropdown-menu dropdown-menu-right">
                <a href="#" class="dropdown-item"><small class="text-muted">Тест сообщений (Сегодня)</small><br>Тест сообщений</a>
                <div class="dropdown-divider"></div>
                <a href="/messages" class="dropdown-item dropdown-link">See all messages</a>
            </div>
        </li>
    </ul>
</nav>
