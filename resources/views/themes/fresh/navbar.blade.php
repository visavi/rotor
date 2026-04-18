<!-- Top Header -->
<header class="app-header">
    <a class="app-header__logo" href="{{ route('home') }}">{{ setting('title') }}</a>

    <ul class="app-nav">
        <li class="app-search">
            <form action="{{ route('search') }}" method="get">
                <input name="query" class="form-control app-search__input" type="search" placeholder="{{ __('main.search') }}" minlength="3" maxlength="64" required>
                <button class="app-search__button"><i class="fa fa-search"></i></button>
            </form>
        </li>
        @hook('navbarStart')

        <li class="dropdown">
            <a href="#" class="app-nav__item" data-bs-toggle="dropdown" aria-expanded="false" data-bs-display="static">
                <i class="fa-regular {{ request()->cookie('theme') === 'dark' ? 'fa-moon' : 'fa-sun' }} fa-lg" id="theme-icon-active"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" style="--bs-dropdown-min-width: 8rem;">
                <li>
                    <a type="button" class="dropdown-item" data-bs-theme-value="light">
                        <i class="fa-regular fa-sun fa-lg"></i>
                        Светлая
                    </a>
                </li>
                <li>
                    <a type="button" class="dropdown-item" data-bs-theme-value="dark">
                        <i class="fa-regular fa-moon fa-lg"></i>
                        Темная
                    </a>
                </li>
            </ul>
        </li>

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

            <!-- User Menu -->
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

<!-- Horizontal Menu -->
<nav class="app-topnav">
    <div class="app-topnav__inner">
        <ul class="app-topnav__menu">
            @hook('sidebarMenuStart')
            <li>
                <a class="app-topnav__item{{ request()->is('forums*', 'topics*') ? ' active' : '' }}" href="{{ route('forums.index') }}">
                    <i class="far fa-comment-alt"></i>
                    <span>{{ __('index.forums') }}</span>
                    <span class="badge menu-badge">{{ statsForum() }}</span>
                </a>
            </li>
            <li>
                <a class="app-topnav__item{{ request()->is('guestbook*') ? ' active' : '' }}" href="{{ route('guestbook.index') }}">
                    <i class="far fa-comment"></i>
                    <span>{{ __('index.guestbook') }}</span>
                    <span class="badge menu-badge">{{ statsGuestbook() }}</span>
                </a>
            </li>
            <li>
                <a class="app-topnav__item{{ request()->is('news*') ? ' active' : '' }}" href="{{ route('news.index') }}">
                    <i class="far fa-newspaper"></i>
                    <span>{{ __('index.news') }}</span>
                    <span class="badge menu-badge">{{ statsNews() }}</span>
                </a>
            </li>
            <li class="dropdown">
                <a class="app-topnav__item{{ request()->is('blogs*', 'articles*') ? ' active' : '' }}" href="#" data-bs-toggle="dropdown">
                    <i class="far fa-sticky-note"></i>
                    <span>{{ __('index.blogs') }}</span>
                    <i class="fa fa-angle-down"></i>
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item{{ request()->routeIs('blogs.index') ? ' active' : '' }}" href="{{ route('blogs.index') }}">{{ __('blogs.blogs_list') }}</a></li>
                    <li><a class="dropdown-item{{ request()->routeIs('blogs.main') ? ' active' : '' }}" href="{{ route('blogs.main') }}">{{ __('blogs.articles_all') }}</a></li>
                    <li><a class="dropdown-item{{ request()->routeIs('articles.index') ? ' active' : '' }}" href="{{ route('articles.index') }}">{{ __('blogs.new_articles') }}</a></li>
                    <li><a class="dropdown-item{{ request()->routeIs('articles.new-comments') ? ' active' : '' }}" href="{{ route('articles.new-comments') }}">{{ __('blogs.new_comments') }}</a></li>
                </ul>
            </li>
            <li class="dropdown">
                <a class="app-topnav__item{{ request()->is('loads*', 'downs*') ? ' active' : '' }}" href="#" data-bs-toggle="dropdown">
                    <i class="fas fa-download"></i>
                    <span>{{ __('index.loads') }}</span>
                    <i class="fa fa-angle-down"></i>
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item{{ request()->routeIs('loads.index') ? ' active' : '' }}" href="{{ route('loads.index') }}">{{ __('loads.loads_list') }}</a></li>
                    <li><a class="dropdown-item{{ request()->routeIs('downs.new-files') ? ' active' : '' }}" href="{{ route('downs.new-files') }}">{{ __('loads.new_downs') }}</a></li>
                    <li><a class="dropdown-item{{ request()->routeIs('downs.new-comments') ? ' active' : '' }}" href="{{ route('downs.new-comments') }}">{{ __('loads.new_comments') }}</a></li>
                </ul>
            </li>
            <li>
                <a class="app-topnav__item{{ request()->is('photos*') ? ' active' : '' }}" href="{{ route('photos.index') }}">
                    <i class="far fa-image"></i>
                    <span>{{ __('index.photos') }}</span>
                    <span class="badge menu-badge">{{ statsPhotos() }}</span>
                </a>
            </li>
            <li>
                <a class="app-topnav__item{{ request()->is('boards*', 'item*') ? ' active' : '' }}" href="{{ route('boards.index') }}">
                    <i class="far fa-rectangle-list"></i>
                    <span>{{ __('index.boards') }}</span>
                    <span class="badge menu-badge">{{ statsBoard() }}</span>
                </a>
            </li>
            <li>
                <a class="app-topnav__item{{ request()->is('votes*') ? ' active' : '' }}" href="{{ route('votes.index') }}">
                    <i class="fas fa-square-poll-horizontal"></i>
                    <span>{{ __('index.votes') }}</span>
                    <span class="badge menu-badge">{{ statVotes() }}</span>
                </a>
            </li>
            @hook('sidebarMenuEnd')
        </ul>

        <div class="app-topnav__right">
            <span class="app-topnav__online">@yield('online')</span>
            <span class="app-topnav__lang">
                <i class="fas fa-globe-americas"></i>
                <a href="{{ route('language', ['lang' => 'ru']) }}{{ returnUrl() }}">RU</a> /
                <a href="{{ route('language', ['lang' => 'en']) }}{{ returnUrl() }}">EN</a>
            </span>
        </div>
    </div>
</nav>
