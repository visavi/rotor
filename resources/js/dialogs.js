// Диалоги подтверждения и ввода значения: по одному <dialog> на каждый вид,
// общие для всего сайта. Браузерные confirm() и prompt() блокируют поток
// и выглядят чужеродно, поэтому наружу отдаются только эти

import { __ } from './translate.js'

function makeDialog(extra = '') {
    const el = document.createElement('dialog')
    el.className = 'confirm-dialog'
    el.innerHTML = `
<p class="confirm-message"></p>
${extra}
<div class="confirm-footer">
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
    el.showModal()
}

/* Подтверждение действия: callback получает true или false */
const confirmDialogEl = makeDialog()

function confirm(message, callback) {
    confirmDialogEl.querySelector('.js-confirm-ok').onclick = () => { confirmDialogEl.close(); callback(true) }
    confirmDialogEl.querySelector('.js-confirm-cancel').onclick = () => { confirmDialogEl.close(); callback(false) }

    open(confirmDialogEl, message)
}

/* Запрос значений: fields — [{ label, value }], callback получает массив строк
 * в том же порядке или null, если отменили */
const promptDialogEl = makeDialog('<div class="js-prompt-fields"></div>')

function promptFields(message, fields, callback) {
    const wrap = promptDialogEl.querySelector('.js-prompt-fields')
    const ok = promptDialogEl.querySelector('.js-confirm-ok')

    wrap.innerHTML = ''

    const inputs = fields.map(({ label, value }) => {
        if (label) {
            const labelEl = document.createElement('label')
            labelEl.className = 'form-label mb-1'
            labelEl.textContent = label
            wrap.appendChild(labelEl)
        }

        const input = document.createElement('input')
        input.type = 'text'
        input.className = 'form-control mb-3'
        input.value = value ?? ''

        // Enter в поле равносилен кнопке ОК: без этого форма диалога отправляет страницу
        input.onkeydown = (event) => {
            if (event.key === 'Enter') {
                event.preventDefault()
                ok.click()
            }
        }

        wrap.appendChild(input)

        return input
    })

    ok.onclick = () => { promptDialogEl.close(); callback(inputs.map(input => input.value)) }
    promptDialogEl.querySelector('.js-confirm-cancel').onclick = () => { promptDialogEl.close(); callback(null) }

    open(promptDialogEl, message)
    inputs[0]?.select()
}

/* Запрос одного значения: callback получает строку или null, если отменили */
function prompt(message, value, callback) {
    promptFields(message, [{ value }], values => callback(values ? values[0] : null))
}

/* Подтверждение перед отправкой формы или переходом по ссылке */
const confirmedElements = new WeakSet()

window.confirmAction = function (el) {
    const message = el.dataset.confirm || __('confirm_action')

    // Форма отправляется повторно уже после ответа диалога, и второй заход
    // пропускается: иначе подтверждение спрашивалось бы по кругу
    if (confirmedElements.has(el)) {
        confirmedElements.delete(el)

        return true
    }

    confirm(message, function (result) {
        if (!result) {
            return
        }

        const form = el.matches('form') ? el : el.closest('form')

        if (form) {
            confirmedElements.add(form)
            form.submit()

            return
        }

        const href = el.getAttribute('href')

        if (href) {
            window.location.href = href
        }
    })

    return false
}

/* Запрос значения перед отправкой формы: ответ уходит в поле по data-field */
window.promptAction = function (el) {
    const form = el.matches('form') ? el : el.closest('form')
    const field = form?.elements[el.dataset.field || 'value']

    prompt(el.dataset.prompt || '', el.dataset.value || '', function (value) {
        if (value === null || value === '') {
            return
        }

        if (field) {
            field.value = value
        }

        form?.submit()
    })

    return false
}

/* Запрос значений промисом: строка (askValue) или массив строк (askValues), null при отмене */
window.askValue = (message, value = '') => new Promise((resolve) => prompt(message, value, resolve))
window.askValues = (message, fields) => new Promise((resolve) => promptFields(message, fields, resolve))

export { confirm, prompt }
