<!-- Simple Footer -->
<footer class="site-footer">
    <div class="wrap">
        @hook('footerStart')

        <div class="site-footer__cols">
            <div>
                <h6>{{ __('index.pages') }}</h6>
                <ul>
                    <li><a href="{{ route('pages') }}">{{ __('index.pages') }}</a></li>
                    @hook('footerColumnStart')
                </ul>
            </div>

            <div>
                <h6>{{ __('index.users') }}</h6>
                <ul>
                    <li>
                        <a href="{{ route('users.index') }}">{{ __('index.users') }}</a>
                        <span class="badge bg-adaptive">{{ statsUsers() }}</span>
                    </li>
                    @hook('footerColumnMiddle')
                </ul>
            </div>

            <div>
                <h6>{{ __('index.mails') }}</h6>
                <ul>
                    <li><a href="{{ route('mails.index') }}">{{ __('index.mails') }}</a></li>
                    <li><a href="{{ route('search') }}">{{ __('index.search') }}</a></li>
                    @hook('footerColumnEnd')
                </ul>
            </div>
        </div>

        <div class="site-footer__counter">{{ showCounter() }}</div>

        <div class="site-footer__bottom">
            <span>{{ setting('copy') }}</span>
            <span class="site-footer__meta">
                {{ showOnline() }}
                <a href="#" data-bs-toggle="modal" data-bs-target="#languageModal">
                    <img src="/assets/flags/{{ app()->getLocale() }}.svg" alt="" width="18" class="flag" onerror="this.remove()">
                    {{ __('main.lang') }}
                </a>
            </span>
        </div>

        @hook('footerEnd')
    </div>
    {{ performance() }}
</footer>
