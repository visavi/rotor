/*
 * Свой JS темы Simple — обычный файл, без сборки.
 * Общий рантайм (Bootstrap, tiptap, ajax ядра) приходит из resources/themes/app.js
 */
document.addEventListener('DOMContentLoaded', () => {
    // Мобильное меню
    const menu = document.querySelector('[data-menu]')

    document.querySelector('[data-menu-toggle]')?.addEventListener('click', () => {
        menu?.classList.toggle('is-open')
    })

    // Ядро само переключает тему по клику на [data-bs-theme-value],
    // здесь только инвертируем значение, чтобы одна кнопка работала в обе стороны
    document.querySelectorAll('[data-bs-theme-value]').forEach(el => {
        el.addEventListener('click', () => {
            el.dataset.bsThemeValue = el.dataset.bsThemeValue === 'dark' ? 'light' : 'dark'
        })
    })
})
