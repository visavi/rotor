{{-- Тема ещё не выбрана — берём системную до отрисовки, иначе тёмная страница мигнёт светлой --}}
@unless (request()->cookie('theme'))
    <script>
        if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.documentElement.setAttribute('data-bs-theme', 'dark')
        }
    </script>
@endunless
