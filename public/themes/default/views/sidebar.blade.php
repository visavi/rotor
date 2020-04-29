<!-- Sidebar menu-->
<div class="app-sidebar__overlay" data-toggle="sidebar"></div>
<aside class="app-sidebar">
    <div class="app-sidebar__user">
        @if ($user = getUser())
            <img class="app-sidebar__user-avatar" src="https://s3.amazonaws.com/uifaces/faces/twitter/jsa/48.jpg" alt="User Image">
            <div>
                <p class="app-sidebar__user-name"><a href="/users/{{ $user->login }}">{{ $user->getName() }}</a></p>
                <p class="app-sidebar__user-designation">{!! getUser()->getStatus() !!}</p>
            </div>
        @else
            <a href="/login{{ returnUrl() }}" rel="nofollow">{{ __('index.login') }}</a>
            <a href="/register" rel="nofollow">{{ __('index.register') }}</a>
        @endif
    </div>
    <ul class="app-menu">
        <li>
            <a class="app-menu__item" href="/forums">
                <i class="app-menu__icon far fa-comment-alt"></i>
                <span class="app-menu__label">{{ __('index.forums') }}</span>
                <span class="badge badge-pill badge-light">{{ statsForum() }}</span>
            </a>
        </li>

        <li>
            <a class="app-menu__item" href="/guestbook">
                <i class="app-menu__icon far fa-comment"></i>
                <span class="app-menu__label">{{ __('index.guestbook') }}</span>
                <span class="badge badge-pill badge-light">{{ statsGuestbook() }}</span>
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
