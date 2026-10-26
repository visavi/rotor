// Диалоги на нативном <dialog>: подтверждение и ввод значения, плюс окна из
// разметки. Классы — как у Dialog в Bootstrap 6. Браузерные confirm() и prompt()
// блокируют поток и выглядят чужеродно, поэтому наружу отдаются только эти

import { __ } from './translate.js'

/* Открытые диалоги, последний — верхний */
const openDialogs = []

// Открытый диалог лежит в верхнем слое, остальная страница под ним недоступна.
// Плашки — popover: повторный показ кладёт их поверх окна. Внутрь окна их не переносим,
// там они въезжали вместе с окном. Объявления для читалки переезжают: страница не озвучивается
function moveToasts(host) {
    document.querySelectorAll('.notyf-announcer').forEach(el => host.appendChild(el))

    const toasts = document.querySelector('.notyf')
    if (host === document.body || !toasts?.showPopover) return

    toasts.popover = 'manual'
    toasts.togglePopover(false)
    toasts.showPopover()
    // Повторный показ перезапускает анимацию появления — показанная плашка моргала бы
    toasts.getAnimations({ subtree: true }).forEach(animation => animation.finish())
}

/* Диалоги, у которых уже есть слушатель закрытия */
const tracked = new WeakSet()

function showDialog(el) {
    if (el.open) return

    el.showModal()

    // Сам showModal() фокусирует первую кнопку, и крестик открывается в обводке
    // фокуса. Фокус получает окно, если в нём нет поля с autofocus
    if (!el.querySelector('[autofocus]')) {
        el.tabIndex = -1
        el.focus()
    }

    openDialogs.push(el)
    moveToasts(el)

    // Esc закрывает диалог без нашего кода: уведомления вернутся по событию close.
    // Событие приходит позже закрытия: если окно уже открыли снова, оно запоздало
    if (!tracked.has(el)) {
        tracked.add(el)
        el.addEventListener('close', () => {
            if (!el.open) forgetDialog(el)
        })
    }
}

function forgetDialog(el) {
    // Вопрос без ответа закрывается отменой (answerOnce)
    el.dismiss?.()

    const index = openDialogs.indexOf(el)
    if (index === -1) return

    openDialogs.splice(index, 1)
    moveToasts(openDialogs.at(-1) ?? document.body)
}

// Событие close приходит отдельной задачей, а в фоновой вкладке откладывается:
// уведомления возвращаются сразу
function hideDialog(el) {
    el.close()
    forgetDialog(el)
}

// Закрытие кнопкой или кликом мимо окна идёт через cancel, как по Esc:
// обработчик может его отменить, например при несохранённом тексте
function closeDialog(el) {
    if (el.dispatchEvent(new Event('cancel', { cancelable: true }))) {
        hideDialog(el)
    }
}

// Клик по самому <dialog> за пределами его рамки — это клик по подложке
function isBackdrop(e) {
    if (!(e.target instanceof HTMLDialogElement)) return false

    const rect = e.target.getBoundingClientRect()

    return e.clientX < rect.left || e.clientX > rect.right || e.clientY < rect.top || e.clientY > rect.bottom
}

// Нажатие запоминается: выделение текста, отпущенное над подложкой, окно не закрывает
let backdropPressed = null

document.addEventListener('pointerdown', (e) => {
    backdropPressed = isBackdrop(e) ? e.target : null
})

// Диалог может лежать в <template>: пока окно не понадобилось, его разметка
// инертна и картинки не грузятся. При первом открытии шаблон разворачивается на месте
function findDialog(selector) {
    const el = document.querySelector(selector)
    if (el) return el

    const template = [...document.querySelectorAll('template')].find(t => t.content.querySelector(selector))
    if (!template) return null

    template.replaceWith(template.content)

    return document.querySelector(selector)
}

/* data-dialog="#id" открывает диалог, data-dialog-close закрывает свой */
document.addEventListener('click', (e) => {
    const opener = e.target.closest('[data-dialog]')

    if (opener) {
        e.preventDefault()
        const el = findDialog(opener.dataset.dialog)
        if (el) showDialog(el)

        return
    }

    const closer = e.target.closest('[data-dialog-close]')

    if (closer) {
        closeDialog(closer.closest('dialog'))

        return
    }

    if (backdropPressed === e.target && isBackdrop(e)) {
        closeDialog(e.target)
    }
})

function makeDialog(extra = '') {
    const el = document.createElement('dialog')
    el.className = 'dialog'
    el.innerHTML = `
<div class="dialog-body confirm-body">
    <p class="confirm-message"></p>
    ${extra}
</div>
<div class="dialog-footer">
    <button type="button" class="btn btn-secondary btn-sm js-confirm-cancel"></button>
    <button type="button" class="btn btn-primary btn-sm js-confirm-ok"></button>
</div>`
    document.body.appendChild(el)

    return el
}

// Кнопки подписываются при каждом показе: переводы подключаются директивой
// @translation и на момент создания диалога могут быть ещё не готовы
function open(el, message) {
    const messageEl = el.querySelector('.confirm-message')
    messageEl.textContent = message
    // Диалог с подписанными полями обходится без заголовка
    messageEl.hidden = !message
    el.querySelector('.js-confirm-ok').textContent = __('buttons.ok')
    el.querySelector('.js-confirm-cancel').textContent = __('buttons.cancel')
    showDialog(el)
}

// Ответ приходит ровно один раз: закрытие окна без кнопки (Esc, клик мимо)
// считается отменой, иначе ожидающий ответа код так и остался бы ждать
function answerOnce(el, callback, cancelValue) {
    // Прежний вопрос в этом же окне, оставшийся без ответа, — отмена
    el.dismiss?.()

    let answered = false

    const answer = (value) => {
        if (answered) return
        answered = true
        el.dismiss = null
        hideDialog(el)
        callback(value)
    }

    el.dismiss = () => answer(cancelValue)

    return answer
}

/* Подтверждение действия: callback получает true или false */
const confirmDialogEl = makeDialog()

function confirm(message, callback) {
    const answer = answerOnce(confirmDialogEl, callback, false)
    confirmDialogEl.querySelector('.js-confirm-ok').onclick = () => answer(true)
    confirmDialogEl.querySelector('.js-confirm-cancel').onclick = () => answer(false)

    open(confirmDialogEl, message)
}

/* Запрос значений: fields — [{ label, value }], callback получает массив строк
 * в том же порядке или null, если отменили */
const promptDialogEl = makeDialog('<div class="d-flex flex-column gap-3 js-prompt-fields"></div>')

function promptFields(message, fields, callback) {
    const wrap = promptDialogEl.querySelector('.js-prompt-fields')
    const ok = promptDialogEl.querySelector('.js-confirm-ok')

    wrap.innerHTML = ''

    const inputs = fields.map(({ label, value }) => {
        const field = document.createElement('div')

        if (label) {
            const labelEl = document.createElement('label')
            labelEl.className = 'form-label mb-1'
            labelEl.textContent = label
            field.appendChild(labelEl)
        }

        const input = document.createElement('input')
        input.type = 'text'
        input.className = 'form-control'
        input.value = value ?? ''

        // Enter в поле равносилен кнопке ОК: без этого форма диалога отправляет страницу
        input.onkeydown = (event) => {
            if (event.key === 'Enter') {
                event.preventDefault()
                ok.click()
            }
        }

        field.appendChild(input)
        wrap.appendChild(field)

        return input
    })

    const answer = answerOnce(promptDialogEl, callback, null)
    ok.onclick = () => answer(inputs.map(input => input.value))
    promptDialogEl.querySelector('.js-confirm-cancel').onclick = () => answer(null)

    open(promptDialogEl, message)
    inputs[0]?.select()
}

/* Запрос одного значения: callback получает строку или null, если отменили */
function prompt(message, value, callback) {
    promptFields(message, [{ value }], values => callback(values ? values[0] : null))
}

/* Подтверждение по data-confirm: у формы — перед отправкой, у кнопки и ссылки — перед кликом.
 * Пустой атрибут — стандартный вопрос, data-confirm-again — второй вопрос для необратимого.
 *
 * После «да» действие повторяется: форма уходит через requestSubmit со своей кнопкой
 * (formaction, name и value) и проходит data-ajax и слушатели страницы, клик доходит
 * до своих обработчиков. Отметка живёт только на этот синхронный повтор: оставшись,
 * она пропустила бы вопрос на странице, возвращённой из bfcache.
 * Слушатели в фазе захвата: отказ останавливает событие раньше остальных */
const confirmedElements = new WeakSet()

function askConfirm(el, proceed) {
    confirm(el.dataset.confirm || __('confirm_action'), function (result) {
        if (!result) {
            return
        }

        if (!el.dataset.confirmAgain) {
            return proceed()
        }

        confirm(el.dataset.confirmAgain, again => again && proceed())
    })
}

function repeatConfirmed(el, action) {
    confirmedElements.add(el)
    action()
    confirmedElements.delete(el)
}

document.addEventListener('submit', function (e) {
    const form = e.target

    if (!form.matches('form[data-confirm]') || confirmedElements.has(form)) {
        return
    }

    e.preventDefault()
    e.stopImmediatePropagation()

    const submitter = e.submitter ?? undefined

    askConfirm(form, () => repeatConfirmed(form, () => form.requestSubmit(submitter)))
}, true)

document.addEventListener('click', function (e) {
    const el = e.target.closest('[data-confirm]:not(form)')

    if (!el || confirmedElements.has(el)) {
        return
    }

    e.preventDefault()
    e.stopImmediatePropagation()

    askConfirm(el, () => repeatConfirmed(el, () => el.click()))
}, true)

/* @deprecated до 15.0: старые шаблоны модулей зовут из onsubmit и onclick.
 * Элемент с data-confirm уже спросил общий слушатель — второй раз не спрашиваем */
window.confirmAction = function (el) {
    if (el.dataset.confirm !== undefined || confirmedElements.has(el)) {
        return true
    }

    askConfirm(el, () => repeatConfirmed(el, () => el.matches('form') ? el.requestSubmit() : el.click()))

    return false
}

/* Запрос значения перед отправкой формы по data-prompt: ответ уходит в поле по data-field */
document.addEventListener('click', function (e) {
    const el = e.target.closest('[data-prompt]')

    if (!el) {
        return
    }

    e.preventDefault()
    promptAction(el)
})

function promptAction(el) {
    const form = el.matches('form') ? el : el.closest('form')
    const field = form?.elements[el.dataset.field || 'value']

    prompt(el.dataset.prompt || '', el.dataset.value || '', function (value) {
        if (value === null || value === '') {
            return
        }

        if (field) {
            field.value = value
        }

        // requestSubmit, а не submit: форма проходит data-ajax и слушатели страницы
        form?.requestSubmit()
    })

    return false
}

// @deprecated до 15.0: старые шаблоны модулей зовут из onclick
window.promptAction = promptAction

/* Запрос значений промисом: строка (askValue) или массив строк (askValues), null при отмене */
window.askValue = (message, value = '') => new Promise((resolve) => prompt(message, value, resolve))
window.askValues = (message, fields) => new Promise((resolve) => promptFields(message, fields, resolve))

export { confirm, prompt, showDialog, hideDialog }
