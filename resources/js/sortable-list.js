// Лёгкий входной файл — грузит Sortable только если на странице есть сортируемый список
document.addEventListener('DOMContentLoaded', () => {
    const lists = document.querySelectorAll('[data-sortable]')
    if (!lists.length) return

    // Vite автоматически выносит динамический импорт в отдельный чанк
    import('./sortable.js').then(({ initSortable }) => initSortable(lists))
})
