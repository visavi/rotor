import Sortable from 'sortablejs'
import { ajax } from './ajax.js'
import { notyf } from './globals.js'
import { __ } from './translate.js'

/**
 * Общее для обоих режимов: тянем за ручку, призрак приглушаем,
 * нативный HTML5 drag капризен на тач-экранах — fallback ведёт себя одинаково везде
 */
const BASE_OPTIONS = {
    animation: 150,
    handle: '[data-sortable-handle]',
    ghostClass: 'sortable-ghost',
    forceFallback: true,
    fallbackTolerance: 3,
}

/**
 * Включает перетаскивание в списках [data-sortable]
 *
 * Порядок элементов пишется в поле, названное в data-sortable-target: форма
 * отправляет его строкой, поэтому лишних запросов при перетаскивании нет.
 *
 * Список без своей формы (вложения: они грузятся и удаляются мимо формы записи)
 * задаёт data-sortable-url — туда после каждого переноса уходит POST с той же
 * строкой в поле sort. Элементы добавляются в список и после загрузки страницы,
 * поэтому порядок собирается в момент отправки
 */
export function initSortable(lists) {
    lists.forEach(list => {
        const target = document.querySelector(list.dataset.sortableTarget)
        const url = list.dataset.sortableUrl

        const order = () => [...list.children]
            .map(item => item.dataset.key)
            .filter(Boolean)
            .join(',')

        const save = () => {
            if (target) target.value = order()
        }

        const send = () => {
            if (!url) return

            ajax({
                url, type: 'post', dataType: 'json', data: { sort: order() },
                success: data => { if (!data.success) notyf.error(data.message) },
                error: () => notyf.error(__('request_failed')),
            })
        }

        Sortable.create(list, {
            ...BASE_OPTIONS,
            onEnd: evt => {
                save()

                if (evt.oldIndex !== evt.newIndex) send()

                // Куда отпустили элемент, решают сами страницы (миниатюра на редакторе — вставка)
                list.dispatchEvent(new CustomEvent('sortable:end', { bubbles: true, detail: evt }))
            },
        })

        save()
    })
}

/**
 * Включает перетаскивание в дереве категорий [data-sortable-tree]
 *
 * Дерево — плоский список: вложенность выражена отступом строки. Движение по
 * вертикали меняет позицию, движение по горизонтали — глубину, как в админках
 * Drupal и XenForo. В поле, названное в data-sortable-target, уходит строка
 * id:parent_id в порядке обхода — её ждёт CategoryTree::reorder
 */
export function initSortableTree(trees) {
    trees.forEach(tree => {
        const target = document.querySelector(tree.dataset.sortableTarget)

        // Ширина уровня объявлена в CSS: держать её вторым числом здесь — значит
        // однажды поправить одно место и забыть другое
        const step = parseFloat(getComputedStyle(tree).getPropertyValue('--depth-step')) || 24

        const rows = () => [...tree.querySelectorAll('li[data-key]')]
        const depthOf = row => Number(row.dataset.depth) || 0

        const setDepth = (row, depth) => {
            row.dataset.depth = depth
            row.style.setProperty('--depth', depth)
        }

        // Родитель строки — ближайшая предыдущая строка уровнем выше.
        // Строки без такой строки сидят в корне
        const save = () => {
            if (!target) return

            const parents = []

            target.value = rows()
                .map(row => {
                    const depth = depthOf(row)
                    parents[depth] = row.dataset.key

                    return `${row.dataset.key}:${depth ? parents[depth - 1] ?? 0 : 0}`
                })
                .join(',')
        }

        // Перетаскиваемая строка и её поддерево
        let dragged = null
        let subtree = []
        let startDepth = 0
        let pointerX = 0

        // Глубже, чем на уровень ниже предыдущей строки, уйти нельзя: иначе
        // раздел повис бы в воздухе, без родителя. Строки — прямые соседи по списку,
        // поэтому предыдущую берём напрямую: обработчик зовётся на каждое движение мыши
        const limit = () => {
            const prev = dragged.previousElementSibling

            return prev ? depthOf(prev) + 1 : 0
        }

        const applyDepth = () => {
            if (!dragged) return

            const shift = Math.round((pointerX - dragged.getBoundingClientRect().left) / step)
            const depth = Math.min(Math.max(shift, 0), limit())

            if (depth !== depthOf(dragged)) {
                setDepth(dragged, depth)
            }
        }

        const onPointerMove = event => {
            pointerX = event.clientX
            applyDepth()
        }

        const onStart = evt => {
            dragged = evt.item
            startDepth = depthOf(dragged)

            // Поддерево на время переноса убираем из списка: тащится одна строка,
            // а дети возвращаются под неё на новое место. Так спокойнее в списке
            // и проще считать глубину — свои же дети не становятся ориентиром
            subtree = []
            let next = dragged.nextElementSibling

            while (next && depthOf(next) > startDepth) {
                subtree.push(next)
                next = next.nextElementSibling
            }

            subtree.forEach(row => row.remove())

            document.body.classList.add('sortable-dragging')
            document.addEventListener('pointermove', onPointerMove)
        }

        const onEnd = () => {
            document.removeEventListener('pointermove', onPointerMove)
            document.body.classList.remove('sortable-dragging')

            applyDepth()

            // Дети возвращаются следом за родителем, сохраняя свою глубину
            // относительно него
            const delta = depthOf(dragged) - startDepth
            let after = dragged

            subtree.forEach(row => {
                setDepth(row, depthOf(row) + delta)
                after.after(row)
                after = row
            })

            dragged = null
            subtree = []

            save()
        }

        Sortable.create(tree, { ...BASE_OPTIONS, onStart, onChange: applyDepth, onEnd })

        save()
    })
}
