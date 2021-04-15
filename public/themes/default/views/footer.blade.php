<footer class="app-footer">
    @yield('counter')

    <div class="float-right">
        <a target="_blank" href="https://telegram.me/visavinet"><i class="fab fa-telegram fa-2x" style="color: #0088cc"></i></a>
        <a target="_blank" href="https://vk.com/visavinet"><i class="fab fa-vk fa-2x" style="color: #45668e"></i></a>
        <a target="_blank" href="https://www.facebook.com/groups/visavinet"><i class="fab fa-facebook-square fa-2x" style="color: #3b5998"></i></a>
    </div>

    <div>{{ setting('copy') }}</div>

    @yield('performance')
</footer>
