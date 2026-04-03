<!-- Cyberpunk Footer -->
<div class="app-footer">
    <footer>
        @hook('footerStart')
        <ul class="cyber-footer-links">
            <li><a class="footer-item" href="{{ route('pages') }}">{{ __('index.pages') }}</a></li>
            <li><a class="footer-item" href="{{ route('users.index') }}">{{ __('index.users') }}</a></li>
            <li><a class="footer-item" href="{{ route('offers.index') }}">{{ __('index.offers') }}</a></li>
            <li><a class="footer-item" href="{{ route('mails.index') }}">{{ __('index.mails') }}</a></li>
            <li><a class="footer-item" href="{{ route('search') }}">{{ __('index.search') }}</a></li>
            @hook('footerColumnStart')
            @hook('footerColumnMiddle')
            @hook('footerColumnEnd')
        </ul>
        <div class="cyber-footer-bottom">
            @yield('counter')
            <div class="cyber-footer-copy">{{ setting('copy') }}</div>
            <div class="cyber-footer-social">
                <a target="_blank" href="https://telegram.me/visavinet"><i class="fab fa-telegram fa-lg"></i></a>
                <a target="_blank" href="https://vk.com/visavinet"><i class="fab fa-vk fa-lg"></i></a>
                <a target="_blank" href="https://www.facebook.com/groups/visavinet"><i class="fab fa-facebook-square fa-lg"></i></a>
            </div>
        </div>
        @hook('footerEnd')
        @yield('performance')
    </footer>
</div>
