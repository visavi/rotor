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
            <form action="{{ route('search') }}" method="get">
                <input name="query" class="form-control app-search__input" type="search" placeholder="{{ __('main.search') }}" minlength="3" maxlength="64" required>
                <button class="app-search__button"><i class="fa fa-search"></i></button>
            </form>
        </li>
        @hook('navbarStart')
        <li class="dropdown">
            <a href="#" class="app-nav__item" data-bs-toggle="dropdown" aria-expanded="false" data-bs-display="static">
                <i class="fa-solid fa-sun fa-lg" id="theme-icon-active"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" style="--bs-dropdown-min-width: 8rem;">
                <li>
                    <a type="button" class="dropdown-item" data-bs-theme-value="light">
                        <i class="fa-solid fa-sun fa-lg"></i>
                        Светлая
                    </a>
                </li>
                <li>
                    <a type="button" class="dropdown-item" data-bs-theme-value="dark">
                        <i class="fa-solid fa-moon fa-lg"></i>
                        Темная
                    </a>
                </li>
            </ul>
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

            @if ($user->newwall && $user->isActive())
                <li>
                    <a class="app-nav__item" href="/walls/{{ $user->login }}" aria-label="{{ __('index.wall_post') }}">
                        <i class="far fa-comments fa-lg"></i>
                        <span class="badge bg-notify">{{ $user->newwall }}</span>
                    </a>
                </li>
            @endif

            @if ($user->isActive())
                @if (strtotime(date('d.m.Y')) <= strtotime(date('03.01.Y', strtotime('+3 days', SITETIME))))
                    <li>
                        <div class="surprise-container" title="{{ __('pages.surprise') }}">
                            <img alt="" src="/assets/img/images/snow.png" class="surprise-background">
                            <a href="/surprise">
                                <img src="/assets/img/images/ded.png" class="surprise-ded" alt="">
                            </a>
                            <img alt="" src="/assets/img/images/glass.png" class="surprise-glasses">
                        </div>
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
                        <li class="app-notification__title">{{ __('messages.new_messages') }}: <span>{{ $user->newprivat }}</span></li>
                        <div class="app-notification__content js-messages"></div>
                        <li class="app-notification__footer"><a class="dropdown-item" href="/messages">{{ __('messages.all_messages') }}</a></li>
                    </ul>
                </li>
            @endif

            <!-- User Menu-->
            <li class="dropdown">
                <a class="app-nav__item" href="#" data-bs-toggle="dropdown" aria-label="Open Profile Menu">
                    <i class="far fa-user fa-lg"></i>
                </a>
                <ul class="app-notification dropdown-menu dropdown-menu-end">
                    @hook('navbarMenuStart')
                    <li><a class="dropdown-item" href="/users/{{ getUser('login') }}"><i class="fas fa-user fa-lg"></i> {{ __('index.my_account') }}</a></li>
                    <li><a class="dropdown-item" href="/profile"><i class="fas fa-user-edit fa-lg"></i> {{ __('index.my_profile') }}</a></li>
                    <li><a class="dropdown-item" href="/accounts"><i class="fas fa-user-cog fa-lg"></i> {{ __('index.my_details') }}</a></li>
                    <li><a class="dropdown-item" href="/settings"><i class="fas fa-cog fa-lg"></i> {{ __('index.my_settings') }}</a></li>
                    @hook('navbarMenuEnd')
                    <li><a class="dropdown-item" href="/logout?_token={{ csrf_token() }}" onclick="return logout(this)"><i class="fas fa-sign-out-alt fa-lg"></i> {{ __('index.logout') }}</a>
                    </li>
                </ul>
            </li>
        @endif
        @hook('navbarEnd')
    </ul>
</header>
