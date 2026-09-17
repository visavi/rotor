import Sortable from 'sortablejs'

/**
 * Включает перетаскивание в списках [data-sortable]
 *
 * Порядок элементов пишется в поле, названное в data-sortable-target: форма
 * отправляет его строкой, поэтому лишних запросов при перетаскивании нет
 */
export function initSortable(lists) {
    lists.forEach(list => {
        const target = document.querySelector(list.dataset.sortableTarget)

        const save = () => {
            if (!target) return

            target.value = [...list.children]
                .map(item => item.dataset.key)
                .filter(Boolean)
                .join(',')
        }

        Sortable.create(list, {
            animation: 150,
            handle: '[data-sortable-handle]',
            ghostClass: 'sortable-ghost',
            // Нативный HTML5 drag капризен на тач-экранах: fallback ведёт себя одинаково везде
            forceFallback: true,
            fallbackTolerance: 3,
            onEnd: save,
        })

        save()
    })
}
