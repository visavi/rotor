// Лёгкий входной файл — грузит тяжёлый TipTap только если на странице есть редактор
document.addEventListener('DOMContentLoaded', () => {
    const textareas = document.querySelectorAll('textarea.markItUp')
    if (!textareas.length) return

    // Vite автоматически выносит динамический импорт в отдельный чанк
    import('./tiptap.js').then(({ initEditors }) => initEditors(textareas))
})
