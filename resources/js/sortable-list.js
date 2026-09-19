// Лёгкий входной файл — грузит Sortable только если на странице есть что перетаскивать
document.addEventListener('DOMContentLoaded', () => {
    const lists = document.querySelectorAll('[data-sortable]')
    const trees = document.querySelectorAll('[data-sortable-tree]')

    if (!lists.length && !trees.length) return

    // Vite автоматически выносит динамический импорт в отдельный чанк
    import('./sortable.js').then(({ initSortable, initSortableTree }) => {
        if (lists.length) initSortable(lists)
        if (trees.length) initSortableTree(trees)
    })
})
