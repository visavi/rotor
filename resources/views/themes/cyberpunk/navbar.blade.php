<!-- Cyberpunk Navbar -->
<header class="app-header">
    <a class="app-icon icon-toggle" href="#" data-bs-toggle="sidebar" aria-label="Toggle Sidebar"></a>
    <a class="app-header__logo" href="{{ route('home') }}">{{ setting('title') }}</a>

    <ul class="app-nav">
        <li class="app-search">
            <form action="{{ route('search') }}" method="get">
                <input name="query" class="form-control app-search__input" type="search" placeholder="{{ __('main.search') }}" minlength="3" maxlength="64" required>
                <button class="app-search__button"><i class="fa fa-search"></i></button>
            </form>
        </li>
        @hook('navbarStart')

        @if ($user = getUser())
            @if (isAdmin())
                @if (statsSpam())
                    <li>
                        <a class="app-nav__item" href="{{ route('admin.spam.index') }}" aria-label="{{ __('index.complains') }}">
                            <i class="far fa-bell fa-lg"></i>
                            <span class="badge bg-notify">{{ statsSpam() }}</span>
                        </a>
                    </li>
                @endif

                @if ($user->newchat < statsNewChat())
                    <li>
                        <a class="app-nav__item" href="{{ route('admin.chats.index') }}" aria-label="{{ __('index.chat') }}">
                            <i class="far fa-bell fa-lg"></i>
                            <span class="badge bg-notify">!</span>
                        </a>
                    </li>
                @endif
            @endif

            @if ($user->newwall && $user->isActive())
                <li>
                    <a class="app-nav__item" href="{{ route('walls.index', ['login' => $user->login]) }}" aria-label="{{ __('index.wall_post') }}">
                        <i class="far fa-comments fa-lg"></i>
                        <span class="badge bg-notify">{{ $user->newwall }}</span>
                    </a>
                </li>
            @endif

            @if ($user->isActive())
                <li class="dropdown js-messages-block">
                    <a class="app-nav__item" href="#" data-bs-toggle="dropdown" aria-label="Show notifications">
                        <i class="far fa-envelope fa-lg"></i>
                        @if ($user->newprivat)
                            <span class="badge bg-notify">{{ $user->newprivat }}</span>
                        @endif
                    </a>
                    <ul class="app-notification dropdown-menu dropdown-menu-end">
                        <li class="app-notification__title">{{ __('messages.new_messages') }}: <span>{{ $user->newprivat }}</span></li>
                        <div class="app-notification__content js-messages"></div>
                        <li class="app-notification__footer"><a class="dropdown-item" href="{{ route('messages.index') }}">{{ __('messages.all_messages') }}</a></li>
                    </ul>
                </li>

                @if (strtotime(date('d.m.Y')) <= strtotime(date('03.01.Y', strtotime('+3 days', SITETIME))))
                    <li>
                        <a class="app-nav__item" href="{{ route('surprise') }}" aria-label="{{ __('pages.surprise') }}">
                            <i class="fa-solid fa-gift fa-lg text-danger"></i>
                        </a>
                    </li>
                @endif
            @endif

            <li class="dropdown">
                <a class="app-nav__item" href="#" data-bs-toggle="dropdown" aria-label="Open Profile Menu">
                    <i class="far fa-user fa-lg"></i>
                </a>
                <ul class="app-notification dropdown-menu dropdown-menu-end">
                    @hook('navbarMenuStart')
                    <li><a class="dropdown-item" href="{{ route('users.user', ['login' => getUser('login')]) }}"><i class="fas fa-user fa-lg"></i> {{ __('index.my_account') }}</a></li>
                    <li><a class="dropdown-item" href="{{ route('profile') }}"><i class="fas fa-user-edit fa-lg"></i> {{ __('index.my_profile') }}</a></li>
                    <li><a class="dropdown-item" href="{{ route('accounts.account') }}"><i class="fas fa-user-cog fa-lg"></i> {{ __('index.my_details') }}</a></li>
                    <li><a class="dropdown-item" href="{{ route('settings') }}"><i class="fas fa-cog fa-lg"></i> {{ __('index.my_settings') }}</a></li>
                    @if (isAdmin())
                        <li><a class="dropdown-item" href="{{ route('admin.index') }}" rel="nofollow"><i class="fas fa-wrench fa-lg"></i> {{ __('index.panel') }}</a></li>
                    @endif
                    <li><a class="dropdown-item" href="{{ route('menu') }}" rel="nofollow"><i class="fas fa-user-cog fa-lg"></i> {{ __('index.menu') }}</a></li>
                    @hook('navbarMenuEnd')
                    <li>
                        <form action="{{ route('logout') }}" method="post" class="d-inline" onsubmit="return confirmAction(this)" data-confirm="{{ __('users.confirm_logout') }}">
                            @csrf
                            <button class="btn btn-link dropdown-item"><i class="fa-solid fa-right-from-bracket fa-lg"></i> {{ __('index.logout') }}</button>
                        </form>
                    </li>
                </ul>
            </li>
        @else
            <li>
                <a class="app-nav__item" href="{{ route('login') }}" aria-label="{{ __('index.login') }}">
                    <i class="fa-solid fa-right-to-bracket fa-lg"></i>
                </a>
            </li>
        @endif
        @hook('navbarEnd')
    </ul>
</header>
