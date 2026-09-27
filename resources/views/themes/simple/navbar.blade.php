<!-- Simple Navbar -->
<header class="site-header">
    <div class="wrap site-header__top">
        <a class="site-logo" href="{{ route('home') }}">{{ setting('title') }}</a>

        <button class="site-menu-toggle" type="button" data-menu-toggle aria-label="Menu"><i class="fas fa-bars"></i></button>

        <ul class="site-header__user">
            <li>
                <a href="#" data-bs-theme-toggle aria-label="{{ __('main.theme') }}">
                    <i class="fa-regular {{ request()->cookie('theme') === 'dark' ? 'fa-moon' : 'fa-sun' }}" id="theme-icon-active"></i>
                </a>
            </li>
            @hook('navbarStart')

            @if ($user = getUser())
                @if (isAdmin() && statsSpam())
                    <li>
                        <a href="{{ route('admin.spam.index') }}" aria-label="{{ __('index.complains') }}">
                            <i class="far fa-bell"></i>
                            <span class="badge bg-notify">{{ statsSpam() }}</span>
                        </a>
                    </li>
                @endif

                @if ($user->isActive())
                    <li>
                        <a href="{{ route('messages.index') }}" aria-label="{{ __('index.mails') }}">
                            <i class="far fa-envelope"></i>
                            <span class="badge bg-notify js-message-count">{{ $user->getCountNewMessages() ?: '' }}</span>
                        </a>
                    </li>
                @endif

                <li class="dropdown">
                    {{-- На узком экране вместо имени иконка: строка шапки не должна переноситься --}}
                    <a href="#" data-bs-toggle="dropdown" aria-label="{{ $user->getName() }}">
                        <i class="far fa-user d-md-none"></i>
                        <span class="d-none d-md-inline">{{ $user->getName() }}</span>
                        <i class="fas fa-caret-down"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        @hook('navbarMenuStart')
                        <a class="dropdown-item" href="{{ route('users.user', ['login' => $user->login]) }}">{{ __('index.my_account') }}</a>
                        <a class="dropdown-item" href="{{ route('profile') }}">{{ __('index.my_profile') }}</a>
                        <a class="dropdown-item" href="{{ route('settings') }}">{{ __('index.my_settings') }}</a>
                        @if (isAdmin())
                            <a class="dropdown-item" href="{{ route('admin.index') }}" rel="nofollow">{{ __('index.panel') }}</a>
                        @endif
                        @hook('navbarMenuEnd')
                        <hr class="dropdown-divider">
                        <form action="{{ route('logout') }}" method="post" onsubmit="return confirmAction(this)" data-confirm="{{ __('users.confirm_logout') }}">
                            @csrf
                            <button class="btn btn-link dropdown-item">{{ __('index.logout') }}</button>
                        </form>
                    </div>
                </li>
            @else
                <li><a href="{{ route('login') }}">{{ __('index.login') }}</a></li>
                <li><a href="{{ route('register') }}">{{ __('index.register') }}</a></li>
            @endif

            @hook('navbarEnd')
        </ul>
    </div>

    <nav class="site-nav" data-menu>
        <div class="wrap">
            @hook('sidebarMenu')

            <form class="site-search" action="{{ route('search') }}" method="get">
                <input name="query" type="search" placeholder="{{ __('main.search') }}" minlength="3" maxlength="64" required>
            </form>
        </div>
    </nav>
</header>
