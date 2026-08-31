<!-- Simple Navbar -->
<header class="site-header">
    <div class="wrap site-header__top">
        <a class="site-logo" href="{{ route('home') }}">{{ setting('title') }}</a>

        <button class="site-menu-toggle" type="button" data-menu-toggle aria-label="Menu"><i class="fas fa-bars"></i></button>

        <div class="site-header__user">
            @hook('navbarStart')

            {{-- Ядро (main.js) слушает клик по [data-bs-theme-value] и меняет иконку #theme-icon-active,
                 а app.js темы после клика инвертирует значение атрибута --}}
            <a href="#" data-bs-theme-value="{{ request()->cookie('theme') === 'dark' ? 'light' : 'dark' }}" aria-label="Theme">
                <i class="fa-regular {{ request()->cookie('theme') === 'dark' ? 'fa-moon' : 'fa-sun' }}" id="theme-icon-active"></i>
            </a>

            @if ($user = getUser())
                @if (isAdmin() && statsSpam())
                    <a class="site-header__badged" href="{{ route('admin.spam.index') }}" aria-label="{{ __('index.complains') }}">
                        <i class="far fa-bell"></i>
                        <span class="site-badge">{{ statsSpam() }}</span>
                    </a>
                @endif

                @if ($user->isActive())
                    <a class="site-header__badged" href="{{ route('messages.index') }}" aria-label="{{ __('index.mails') }}">
                        <i class="far fa-envelope"></i>
                        @if ($user->newprivat)
                            <span class="site-badge">{{ $user->newprivat }}</span>
                        @endif
                    </a>
                @endif

                <span class="dropdown">
                    <a href="#" data-bs-toggle="dropdown">{{ $user->getName() }} <i class="fas fa-caret-down"></i></a>
                    <span class="dropdown-menu dropdown-menu-end">
                        @hook('navbarMenuStart')
                        <a class="dropdown-item" href="{{ route('users.user', ['login' => $user->login]) }}">{{ __('index.my_account') }}</a>
                        <a class="dropdown-item" href="{{ route('profile') }}">{{ __('index.my_profile') }}</a>
                        <a class="dropdown-item" href="{{ route('accounts.account') }}">{{ __('index.my_details') }}</a>
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
                    </span>
                </span>
            @else
                <a href="{{ route('login') }}">{{ __('index.login') }}</a>
                <a href="{{ route('register') }}">{{ __('index.register') }}</a>
            @endif

            @hook('navbarEnd')
        </div>
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
