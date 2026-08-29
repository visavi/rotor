<!-- Simple Footer -->
<footer class="site-footer">
    <div class="wrap">
        @hook('footerStart')
        <div class="site-footer__links">
            <a href="{{ route('pages') }}">{{ __('index.pages') }}</a>
            <span><a href="{{ route('users.index') }}">{{ __('index.users') }}</a> <span class="badge bg-adaptive">{{ statsUsers() }}</span></span>
            <a href="{{ route('mails.index') }}">{{ __('index.mails') }}</a>
            <a href="{{ route('search') }}">{{ __('index.search') }}</a>
            @hook('footerColumnStart')
            @hook('footerColumnMiddle')
            @hook('footerColumnEnd')
        </div>

        <div class="site-footer__bottom">
            <span>{{ setting('copy') }}</span>
            <span class="site-footer__meta">
                {{ showOnline() }}
                <a href="#" data-bs-toggle="modal" data-bs-target="#languageModal">
                    <img src="/assets/flags/{{ app()->getLocale() }}.svg" alt="" width="18" class="flag" onerror="this.remove()">
                    {{ __('main.lang') }}
                </a>
                {{ showCounter() }}
            </span>
        </div>
        @hook('footerEnd')
    </div>
    {{ performance() }}
</footer>
