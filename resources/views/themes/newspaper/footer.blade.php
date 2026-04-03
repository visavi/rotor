<!-- Newspaper Footer -->
<div class="app-footer">
    <footer>
        @hook('footerStart')
        <div class="paper-footer-cols">
            <div class="paper-footer-col">
                <h5>{{ __('index.pages') }}</h5>
                <ul>
                    <li><a class="footer-item" href="{{ route('pages') }}">{{ __('index.pages') }}</a></li>
                    @hook('footerColumnStart')
                </ul>
            </div>
            <div class="paper-footer-col">
                <h5>{{ __('index.users') }}</h5>
                <ul>
                    <li><a class="footer-item" href="{{ route('users.index') }}">{{ __('index.users') }}</a> <span class="paper-count">{{ statsUsers() }}</span></li>
                    <li><a class="footer-item" href="{{ route('offers.index') }}">{{ __('index.offers') }}</a> <span class="paper-count">{{ statsOffers() }}</span></li>
                    @hook('footerColumnMiddle')
                </ul>
            </div>
            <div class="paper-footer-col">
                <h5>{{ __('index.mails') }}</h5>
                <ul>
                    <li><a class="footer-item" href="{{ route('mails.index') }}">{{ __('index.mails') }}</a></li>
                    <li><a class="footer-item" href="{{ route('search') }}">{{ __('index.search') }}</a></li>
                    @hook('footerColumnEnd')
                </ul>
            </div>
        </div>
        <div class="paper-footer-bottom">
            @yield('counter')
            <div class="paper-footer-social">
                <a target="_blank" href="https://telegram.me/visavinet"><i class="fab fa-telegram fa-lg"></i></a>
                <a target="_blank" href="https://vk.com/visavinet"><i class="fab fa-vk fa-lg"></i></a>
                <a target="_blank" href="https://www.facebook.com/groups/visavinet"><i class="fab fa-facebook-square fa-lg"></i></a>
            </div>
            <div class="paper-footer-copy">{{ setting('copy') }}</div>
        </div>
        @hook('footerEnd')
        @yield('performance')
    </footer>
</div>
