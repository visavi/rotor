<!-- Sidebar menu-->
<div class="app-sidebar__overlay" data-toggle="sidebar"></div>
<aside class="app-sidebar">
    @if ($user = getUser())
        <div class="app-sidebar__user">
            <div class="app-sidebar__user-avatar">
                {!! $user->getAvatar() !!}
            </div>
            <div>
                <p class="app-sidebar__user-name"><a href="/users/{{ $user->login }}">{{ $user->getName() }}</a></p>
                <p class="app-sidebar__user-designation">{!! $user->getStatus() !!}</p>
            </div>
        </div>
    @else
        <div class="app-sidebar__user" href="#collapseLogin" data-toggle="collapse">
            <div class="app-sidebar__user-avatar">
                <img class="img-fluid rounded-circle avatar-default" src="/assets/img/images/avatar_guest.png" alt="">
            </div>
            <div>
                <p class="app-sidebar__user-name">{{ __('users.enter') }}</p>
            </div>
        </div>

        <div class="collapse" id="collapseLogin">
            <ul class="">
                <li>
                    <a class="{{ request()->is('login') ? ' active' : '' }}" href="/login{{ returnUrl() }}" rel="nofollow">
                        <i class="app-menu__icon fas fa-sign-in-alt"></i>
                        <span class="app-menu__label">{{ __('index.login') }}</span>
                    </a>
                </li>
                <li>
                    <a class="{{ request()->is('register') ? ' active' : '' }}" href="/register" rel="nofollow">
                        <i class="app-menu__icon far fa-user"></i>
                        <span class="app-menu__label">{{ __('index.register') }}</span>
                    </a>
                </li>
            </ul>
        </div>
    @endif

    <ul class="app-menu">
        <li>
            <a class="app-menu__item{{ request()->is('forums*', 'topics*') ? ' active' : '' }}" href="/forums">
                <i class="app-menu__icon far fa-comment-alt"></i>
                <span class="app-menu__label">{{ __('index.forums') }}</span>
                <span class="badge badge-pill badge-light">{{ statsForum() }}</span>
            </a>
        </li>

        <li>
            <a class="app-menu__item{{ request()->is('guestbook*') ? ' active' : '' }}" href="/guestbook">
                <i class="app-menu__icon far fa-comment"></i>
                <span class="app-menu__label">{{ __('index.guestbook') }}</span>
                <span class="badge badge-pill badge-light">{{ statsGuestbook() }}</span>
            </a>
        </li>

        <li>
            <a class="app-menu__item{{ request()->is('news*') ? ' active' : '' }}" href="/news">
                <i class="app-menu__icon far fa-comment"></i>
                <span class="app-menu__label">{{ __('index.news') }}</span>
                <span class="badge badge-pill badge-light">{{ statsNews() }}</span>
            </a>
        </li>

        <li class="treeview">
            <a class="app-menu__item" href="#" data-toggle="treeview">
                <i class="app-menu__icon fas fa-info-circle"></i>
                <span class="app-menu__label">{{ __('index.information') }}</span>
                <i class="treeview-indicator fa fa-angle-right"></i>
            </a>
            <ul class="treeview-menu">
                <li><a class="treeview-item" href="/rules"><i class="icon far fa-circle"></i> {{ __('index.site_rules') }}</a></li>
                <li><a class="treeview-item" href="/tags"><i class="icon far fa-circle"></i> {{ __('index.tag_help') }}</a></li>
                <li><a class="treeview-item" href="/stickers"><i class="icon far fa-circle"></i> {{ __('index.stickers_help') }}</a></li>
                <li><a class="treeview-item" href="/faq"><i class="icon far fa-circle"></i> {{ __('index.faq') }}</a></li>
                <li><a class="treeview-item" href="/api"><i class="icon far fa-circle"></i> {{ __('index.api_interface') }}</a></li>
                <li><a class="treeview-item" href="/ratinglists"><i class="icon far fa-circle"></i> {{ __('index.riches_rating') }}</a></li>
                <li><a class="treeview-item" href="/authoritylists"><i class="icon far fa-circle"></i> {{ __('index.reputation_rating') }}</a></li>
                <li><a class="treeview-item" href="/statusfaq"><i class="icon far fa-circle"></i> {{ __('index.user_statuses') }}</a></li>
                <li><a class="treeview-item" href="/who"><i class="icon far fa-circle"></i> {{ __('index.who_online') }}</a></li>
            </ul>
        </li>
    </ul>
</aside>
