<div class="backgr_top">
    <div class="content">
        <div class="logo">
            <a href="{{ route('home') }}"><img src="{{ setting('logotip') }}" alt="{{ setting('title') }}"></a>

            <div class="ms-auto d-flex align-items-center gap-2">
                <ul class="hooks-nav">
                    @hook('navbarStart')
                    @hook('navbarEnd')
                </ul>
                @if (($user = auth()->user()) && $user->isActive())
                    <div>
                        <a class="d-flex align-items-center gap-1" href="{{ route('messages.index') }}" aria-label="{{ __('index.private_message') }}"><i class="far fa-envelope fa-lg"></i><span class="badge rounded-pill bg-danger js-message-count">{{ $user->getCountNewMessages() ?: '' }}</span></a>
                    </div>
                @endif
                <div>
                    <a href="#" data-dialog="#languageDialog"><img src="/assets/flags/{{ app()->getLocale() }}.svg" alt="" width="22" class="me-1 flag" onerror="this.remove()"> {{ __('main.lang') }}</a>
                </div>
            </div>
        </div>

        <div class="menu">
            <span class="mright">
                @include('themes/motor/menu')
            </span>
            <ul class="menu-nav">
                @hook('sidebarMenu')
            </ul>
        </div>
    </div>
</div>
