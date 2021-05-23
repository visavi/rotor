<!-- Navbar-->
<header class="app-header">
    <a class="app-header__logo" href="/">{{ setting('title') }}</a>

    <!-- Sidebar toggle button-->
    <a class="app-icon icon-toggle" href="#" data-bs-toggle="sidebar" aria-label="Show Sidebar"></a>
    @if (! getUser())
        <a class="app-icon icon-login" href="/login" aria-label="{{ __('index.login') }}"></a>
    @endif

    <!-- Navbar Right Menu-->
    <ul class="app-nav">
        <li class="app-search search-navbar">
            <form action="/search" method="get">
                <input name="q" class="app-search__input" type="search" placeholder="{{ __('main.search') }}" required>
                <button class="app-search__button"><i class="fa fa-search"></i></button>
            </form>
        </li>

        <!--Notification Menu-->
        @if ($user = getUser())
            @if (isAdmin())
                @if (statsSpam())
                    <li>
                        <a class="app-nav__item" href="/admin/spam" aria-label="{{ __('index.complains') }}">
                            <i class="far fa-bell fa-lg"></i>
                            <span class="badge bg-notify">{{ statsSpam() }}</span>
                        </a>
                    </li>
                @endif

                @if ($user->newchat < statsNewChat())
                    <li>
                        <a class="app-nav__item" href="/admin/chats" aria-label="{{ __('index.chat') }}">
                            <i class="far fa-bell fa-lg"></i>
                            <span class="badge bg-notify">!</span>
                        </a>
                    </li>
                @endif
            @endif

            @if ($user->newwall)
                <li>
                    <a class="app-nav__item" href="/walls/{{ $user->login }}" aria-label="{{ __('index.wall_post') }}">
                        <i class="far fa-comments fa-lg"></i>
                        <span class="badge bg-notify">{{ $user->newwall }}</span>
                    </a>
                </li>
            @endif

            <li class="dropdown js-messages-block">
                <a class="app-nav__item" href="#" data-bs-toggle="dropdown" aria-label="Show notifications">
                    <i class="far fa-envelope fa-lg"></i>
                    @if ($user->newprivat)
                        <span class="badge bg-notify">{{ $user->newprivat }}</span>
                    @endif
                </a>
                <ul class="app-notification dropdown-menu dropdown-menu-end">
                    <li class="app-notification__title">{{ __('messages.new_messages') }}: {{ $user->newprivat }}</li>
                    <div class="app-notification__content js-messages"></div>
                    <li class="app-notification__footer"><a href="/messages">{{ __('messages.all_messages') }}</a></li>
                </ul>
            </li>
            <!-- User Menu-->
            <li class="dropdown">
                <a class="app-nav__item" href="#" data-bs-toggle="dropdown" aria-label="Open Profile Menu">
                    <i class="far fa-user fa-lg"></i>
                </a>
                <ul class="app-notification dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="/users/{{ getUser('login') }}"><i class="fas fa-user fa-lg"></i> {{ __('index.my_account') }}</a></li>
                    <li><a class="dropdown-item" href="/profile"><i class="fas fa-user-edit fa-lg"></i> {{ __('index.my_profile') }}</a></li>
                    <li><a class="dropdown-item" href="/accounts"><i class="fas fa-user-cog fa-lg"></i> {{ __('index.my_details') }}</a></li>
                    <li><a class="dropdown-item" href="/settings"><i class="fas fa-cog fa-lg"></i> {{ __('index.my_settings') }}</a></li>
                    <li><a class="dropdown-item" href="/logout?token={{ csrf_token() }}"><i class="fas fa-sign-out-alt fa-lg"></i> {{ __('index.logout') }}</a></li>
                </ul>
            </li>
        @endif
    </ul>
</header>
