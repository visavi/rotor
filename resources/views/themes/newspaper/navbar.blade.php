<!-- Newspaper Header -->
<header class="app-header">
    <div class="paper-masthead">
        <div class="paper-date">{{ dateFixed(now(), 'd F Y') }}</div>
        <a class="paper-title" href="{{ route('home') }}">{{ setting('title') }}</a>
        <div class="paper-actions">
            <form action="{{ route('search') }}" method="get" class="paper-search">
                <input name="query" class="paper-search__input" type="search" placeholder="{{ __('main.search') }}" minlength="3" maxlength="64" required>
                <button class="paper-search__btn"><i class="fa fa-search"></i></button>
            </form>
            @if ($user = getUser())
                <div class="dropdown paper-user">
                    <a href="#" class="paper-user__link" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle"></i> {{ $user->login }}
                        @if ($user->newprivat)<span class="badge bg-danger">{{ $user->newprivat }}</span>@endif
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end paper-dropdown">
                        @hook('navbarMenuStart')
                        @if ($user->isActive())
                            <li><a class="dropdown-item" href="{{ route('messages.index') }}">{{ __('messages.all_messages') }}@if ($user->newprivat) <span class="badge bg-danger">{{ $user->newprivat }}</span>@endif</a></li>
                            <li><hr class="dropdown-divider"></li>
                        @endif
                        <li><a class="dropdown-item" href="{{ route('users.user', ['login' => $user->login]) }}">{{ __('index.my_account') }}</a></li>
                        <li><a class="dropdown-item" href="{{ route('profile') }}">{{ __('index.my_profile') }}</a></li>
                        <li><a class="dropdown-item" href="{{ route('settings') }}">{{ __('index.my_settings') }}</a></li>
                        @hook('navbarMenuEnd')
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="post" class="d-inline" onsubmit="return confirmAction(this)" data-confirm="{{ __('users.confirm_logout') }}">
                                @csrf
                                <button class="btn btn-link dropdown-item">{{ __('index.logout') }}</button>
                            </form>
                        </li>
                    </ul>
                </div>
            @else
                <a class="paper-login-btn" href="{{ route('login') }}">{{ __('index.login') }}</a>
            @endif
            <a class="paper-sidebar-toggle" href="#" data-bs-toggle="sidebar" aria-label="Toggle Sidebar">
                <i class="fas fa-bars"></i>
            </a>
        </div>
    </div>

    <!-- Section Navigation -->
    <nav class="paper-nav">
        <ul class="paper-nav__list">
            @hook('navbarStart')
            <li><a class="paper-nav__item{{ request()->is('forums*', 'topics*') ? ' active' : '' }}" href="{{ route('forums.index') }}">{{ __('index.forums') }}</a></li>
            <li><a class="paper-nav__item{{ request()->is('guestbook*') ? ' active' : '' }}" href="{{ route('guestbook.index') }}">{{ __('index.guestbook') }}</a></li>
            <li><a class="paper-nav__item{{ request()->is('news*') ? ' active' : '' }}" href="{{ route('news.index') }}">{{ __('index.news') }}</a></li>
            <li><a class="paper-nav__item{{ request()->is('blogs*', 'articles*') ? ' active' : '' }}" href="{{ route('blogs.index') }}">{{ __('index.blogs') }}</a></li>
            <li><a class="paper-nav__item{{ request()->is('loads*', 'downs*') ? ' active' : '' }}" href="{{ route('loads.index') }}">{{ __('index.loads') }}</a></li>
            <li><a class="paper-nav__item{{ request()->is('photos*') ? ' active' : '' }}" href="{{ route('photos.index') }}">{{ __('index.photos') }}</a></li>
            <li><a class="paper-nav__item{{ request()->is('boards*', 'item*') ? ' active' : '' }}" href="{{ route('boards.index') }}">{{ __('index.boards') }}</a></li>
            <li><a class="paper-nav__item{{ request()->is('votes*') ? ' active' : '' }}" href="{{ route('votes.index') }}">{{ __('index.votes') }}</a></li>
            @hook('navbarEnd')
        </ul>
    </nav>
</header>
