<div class="app-sidebar">
    <div class="sidebar-header">

        @if ($user = getUser())
            <div class="">
                {!! $user->getAvatar() !!}
            </div>
            <div class="username">
                <span>{{ $user->getName() }}</span>
                <small>{!! $user->getStatus() !!}</small>
            </div>
        @else
            <a class="btn btn-info" href="/login{{ returnUrl() }}" rel="nofollow">Авторизация</a>
            <a class="btn btn-info" href="register" rel="nofollow">Регистрация</a>
        @endif
    </div>

    <div id="sidebar-nav" class="sidebar-nav">
        <ul>
            <li>
                <a href="/forums">
                    <span class="sidebar-nav-icon far fa-comment-alt"></span>
                    <span class="sidebar-nav-text">{{ __('index.forums') }}</span>
                    <span class="badge badge-pill badge-info">{{ statsForum() }}</span>
                </a>
            </li>
            <li>
                <a href="/guestbooks">
                    <span class="sidebar-nav-icon far fa-comment"></span>
                    <span class="sidebar-nav-text">{{ __('index.guestbooks') }}</span>
                    <span class="badge badge-pill badge-info">{{ statsGuestbook() }}</span>
                </a>
            </li>
            <li>
                <a href="#reference" data-toggle="collapse" aria-expanded="false" class="collapsed">
                    <span class="sidebar-nav-icon fas fa-info-circle"></span>
                    <span class="sidebar-nav-text">{{ __('index.information') }}</span>
                    <i class="fas fa-angle-down"></i>
                </a>
                <ul id="reference" class="collapse" data-parent="#sidebar-nav" style="">
                    <li><a href="/rules">{{ __('index.site_rules') }}</a></li>
                    <li><a href="/tags">{{ __('index.tag_help') }}</a></li>
                    <li><a href="/stickers">{{ __('index.stickers_help') }}</a></li>
                    <li><a href="/faq">{{ __('index.faq') }}</a></li>
                    <li><a href="/api">{{ __('index.api_interface') }}</a></li>
                    <li><a href="/ratinglists">{{ __('index.riches_rating') }}</a></li>
                    <li><a href="/authoritylists">{{ __('index.reputation_rating') }}</a></li>
                    <li><a href="/statusfaq">{{ __('index.user_statuses') }}</a></li>
                    <li><a href="/who">{{ __('index.who_online') }}</a></li>
                </ul>
            </li>
        </ul>
        @if ($user = getUser())
            <hr>
            <ul>
                <li>
                    <a href="/menu">
                        <span class="sidebar-nav-icon fa fa-list"></span>
                        <span class="sidebar-nav-text">Меню</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <span class="sidebar-nav-icon fa fa-power-off"></span>
                        <span class="sidebar-nav-text">Log out</span>
                    </a>
                </li>
            </ul>
        @endif
    </div>
</div>
