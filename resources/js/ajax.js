// Запросы к серверу: транспорт поверх fetch и декларативный слой над ним

import { __ } from './translate.js'
import { notyf } from './globals.js'
import { confirm } from './dialogs.js'

export const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content

/* Транспорт: колбэки вместо промисов — так его зовут со всего сайта */
export function ajax({ url, type = 'GET', data = null, dataType = 'json', beforeSend, complete, success, error }) {
    if (beforeSend) beforeSend()

    const method = type.toUpperCase()

    const options = {
        method,
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            // Без Accept контроллеры с wantsJson() отвечают редиректом, а не json
            'Accept': dataType === 'json' ? 'application/json' : '*/*',
        }
    }

    let target = url

    if (data) {
        if (method === 'GET' || method === 'HEAD') {
            // Запрос с телом fetch отклоняет, поэтому данные уходят в адрес
            const query = new URLSearchParams(data instanceof FormData ? [...data.entries()] : data).toString()

            if (query) target += (url.includes('?') ? '&' : '?') + query
        } else if (data instanceof FormData) {
            options.body = data
        } else {
            options.headers['Content-Type'] = 'application/x-www-form-urlencoded'
            options.body = new URLSearchParams(data)
        }
    }

    fetch(target, options)
        .then(res => dataType === 'json' ? ajaxJson(res) : res.text())
        .then(responseData => { if (success) success(responseData) })
        .catch(err => { if (error) error(null, err.message, err) })
        .finally(() => { if (complete) complete() })
}

/* Вместо json бывает страница ошибки nginx или PHP: 413 на большой файл, 502.
 * Разбор падал с «Unexpected token '<'» — отказ уходит в error с кодом ответа */
function ajaxJson(res) {
    return res.json()
        .catch(() => {
            throw new Error(res.status === 413 ? __('request_too_large') : 'HTTP ' + res.status)
        })
        .then(body => ajaxValidation(res, body))
}

/* Ошибки FormRequest и $request->validate() приходят 422 с {message, errors}:
 * приводим к контракту {success, message} — по первой ошибке каждого поля.
 * 413 от Laravel (post_max_size PHP) приходит json без success */
function ajaxValidation(res, body) {
    if (res.status === 413) return { success: false, message: __('request_too_large') }

    if (res.status !== 422 || !body?.errors) return body

    return { success: false, message: Object.values(body.errors).map(messages => messages[0]).join(' ') }
}

/* Декларативный ajax
 *
 * Разметка вместо своего обработчика на каждый случай: форма или ссылка
 * с data-ajax уходит запросом, а ответ вида {success, message, html}
 * применяется к странице. Слушатели висят на document, поэтому работают
 * и для узлов, добавленных позже — подгруженной ленты, модалок.
 *
 * data-ajax          включает перехват (submit у формы, click у остальных)
 * data-ajax-url      адрес запроса; форма берёт action, ссылка — href
 * data-ajax-method   метод; форма берёт method, остальные — post
 * data-ajax-replace  куда положить html из ответа: self или селектор элемента выше по дереву
 * data-ajax-swap     outer — заменить найденный элемент целиком, а не его содержимое
 * data-ajax-icon     новые классы иконки внутри элемента, когда результат известен заранее
 * data-ajax-remove   что убрать со страницы при успехе (тот же поиск, что у replace)
 * data-ajax-confirm  спросить перед отправкой; пустой атрибут — стандартный текст про удаление
 *
 * Остальные data-атрибуты не-формы уходят в тело запроса.
 */
const ajaxReserved = ['ajax', 'ajaxUrl', 'ajaxMethod', 'ajaxReplace', 'ajaxSwap', 'ajaxIcon', 'ajaxRemove', 'ajaxConfirm', 'ajaxLoading']

function ajaxElement(el, selector) {
    if (!selector) return null

    return selector === 'self' ? el : el.closest(selector)
}

function ajaxPayload(el, submitter) {
    if (el.matches('form')) {
        const data = new FormData(el)
        // Кнопка, которой отправили форму, в FormData сама не попадает,
        // а формы с несколькими кнопками шлют выбор именно в ней
        if (submitter?.name) data.append(submitter.name, submitter.value)

        return data
    }

    const data = {}
    for (const [key, value] of Object.entries(el.dataset)) {
        if (!ajaxReserved.includes(key)) data[key] = value
    }

    return data
}

function ajaxSend(el, submitter) {
    const url = el.dataset.ajaxUrl || el.getAttribute('action') || el.getAttribute('href')

    // Пока запрос в пути, повторные клики игнорируются
    if (!url || el.dataset.ajaxLoading) return

    const method = el.dataset.ajaxMethod || (el.matches('form') ? el.method : 'post')
    // Данные собираются до блокировки: отключённые поля в FormData не попадают
    const data = ajaxPayload(el, submitter)
    // У кнопки без type submit подразумевается, поэтому ловится и она
    const button = el.matches('form') ? el.querySelector('[type="submit"], button:not([type])') : null

    el.dataset.ajaxLoading = '1'
    if (button) button.disabled = true

    ajax({
        url, data, type: method, dataType: 'json',
        complete: () => {
            delete el.dataset.ajaxLoading
            if (button) button.disabled = false
        },
        error: () => notyf.error(__('request_failed')),
        success: (response) => {
            // Молча выходим, если сервер отклонил запрос без пояснения
            if (!response.success) {
                if (response.message) notyf.error(response.message)
                return
            }

            if (response.message) notyf.success(response.message)

            const replace = ajaxElement(el, el.dataset.ajaxReplace)

            if (replace && response.html !== undefined) {
                // outer позволяет вьюхе отдавать блок вместе с его обёрткой
                if (el.dataset.ajaxSwap === 'outer') {
                    replace.outerHTML = response.html
                } else {
                    replace.innerHTML = response.html
                }
            }

            if (el.dataset.ajaxIcon) {
                const icon = el.querySelector('i')
                if (icon) icon.className = el.dataset.ajaxIcon
            }

            ajaxElement(el, el.dataset.ajaxRemove)?.remove()

            if (response.redirect) window.location.href = response.redirect
        }
    })
}

function ajaxHandle(el, event) {
    event.preventDefault()

    if (!('ajaxConfirm' in el.dataset)) {
        ajaxSend(el, event.submitter)
        return
    }

    // Пустой data-ajax-confirm — спросить обычным текстом про удаление записи
    const message = el.dataset.ajaxConfirm || __('confirm_message_delete')

    confirm(message, (result) => { if (result) ajaxSend(el, event.submitter) })
}

document.addEventListener('submit', function (event) {
    const form = event.target.closest('form[data-ajax]')
    if (form) ajaxHandle(form, event)
})

document.addEventListener('click', function (event) {
    const el = event.target.closest('[data-ajax]:not(form)')
    if (el) ajaxHandle(el, event)
})
