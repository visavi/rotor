import * as bootstrap from 'bootstrap'
import { __ } from './translate.js'
import { ajax } from './ajax.js'
import { confirm } from './dialogs.js'
import { renderFile } from './attachments.js'
import { notyf, tags, fancybox, fancyCarousel, fancyCarouselPlugins } from './globals.js'
import './tiptap-editor.js'
import './sortable-list.js'
import './prettify.js'

// Уведомления зовут из разметки: inline-скрипты шаблонов и модулей делают
// notyf.error(...) прямо на странице. Остальные библиотеки нужны только здесь
window.notyf = notyf

// Высота липкой шапки — на неё сдвигается скролл к якорю, иначе цель уходит под шапку
function getNavbarHeight() {
    let max = 0
    document.querySelectorAll('.app-header, .app-topnav').forEach(el => {
        max = Math.max(max, el.getBoundingClientRect().bottom)
    })
    if (!max) {
        // Сторонняя тема без классов ядра: шапкой считается широкий fixed-блок,
        // прижатый к верху; условия отсекают кнопку «наверх», сайдбар и модалки
        document.querySelectorAll('body > *, body > * > *').forEach(el => {
            if (window.getComputedStyle(el).position === 'fixed') {
                const rect = el.getBoundingClientRect()
                if (rect.top >= 0 && rect.top < 5 && rect.bottom > 0
                    && rect.bottom < window.innerHeight * 0.5
                    && rect.width > window.innerWidth * 0.5) {
                    max = Math.max(max, rect.bottom)
                }
            }
        })
    }
    return max
}

// Прокрутка к элементу с поправкой на липкую шапку
function scrollToElement(el, behavior = 'smooth') {
    window.scrollTo({ top: el.getBoundingClientRect().top + window.scrollY - getNavbarHeight(), behavior })
}

// Длинные тексты сворачиваются до --short-view-max, но кнопка нужна только когда
// скрыто заметно много. Высота блока меняется после разметки (картинки, шрифты,
// поворот экрана), поэтому решение пересматривает ResizeObserver, а не таймер
const shortViewObserver = new ResizeObserver(entries => {
    entries.forEach(entry => queueShortView(entry.target))
})

const shortViewQueue = new Set()
let shortViewFrame = null

// Пачка картинок догружается лавиной — пересчитываем одним проходом.
// Таймер, а не requestAnimationFrame: в фоновой вкладке кадры не идут,
// и пост оставался бы обрезанным без кнопки до переключения на вкладку
function queueShortView(el) {
    shortViewQueue.add(el)
    if (shortViewFrame) {
        return
    }

    shortViewFrame = setTimeout(() => {
        shortViewFrame = null
        const queue = [...shortViewQueue]
        shortViewQueue.clear()
        queue.forEach(updateShortView)
    }, 50)
}

function createShortViewButton(el) {
    const btn = document.createElement('button')
    btn.type = 'button'
    btn.className = 'btn btn-sm btn-adaptive mt-2 short-view-toggle'
    btn.textContent = __('buttons.show_full')
    btn.addEventListener('click', function () {
        el.classList.add('expanded')
        el.classList.remove('clamped')
        shortViewObserver.unobserve(el)
        btn.remove()
    })

    return btn
}

function updateShortView(el) {
    if (!el.isConnected || el.classList.contains('expanded')) {
        return
    }

    // scrollHeight отдаёт полную высоту содержимого и в свёрнутом виде
    const maxHeight = parseFloat(getComputedStyle(el).getPropertyValue('--short-view-max')) || 0
    const btn = el.nextElementSibling?.classList.contains('short-view-toggle') ? el.nextElementSibling : null

    if (maxHeight && el.scrollHeight - maxHeight > 100) {
        el.classList.add('clamped')
        if (!btn) {
            el.after(createShortViewButton(el))
        }
    } else {
        el.classList.remove('clamped')
        btn?.remove()
    }
}

function initShortView(container = document) {
    container.querySelectorAll('.section-content.short-view:not(.expanded)').forEach(function (el) {
        if (el.dataset.shortView) {
            return
        }

        el.dataset.shortView = '1'
        shortViewObserver.observe(el)
        updateShortView(el)
    })
}

function applyMask(el, mask) {
    el.addEventListener('input', function () {
        const digits = el.value.replace(/\D/g, '')
        let result = ''
        let di = 0
        for (let i = 0; i < mask.length && di < digits.length; i++) {
            result += mask[i] === '0' ? digits[di++] : mask[i]
        }
        el.value = result
    })
}

document.addEventListener('DOMContentLoaded', function () {
    // Алерты с data-autohide гаснут сами, наведение мыши откладывает закрытие
    document.querySelectorAll('.alert[data-autohide]').forEach(el => {
        const delay = parseInt(el.dataset.autohide, 10) || 5000
        let timer = setTimeout(() => bootstrap.Alert.getOrCreateInstance(el).close(), delay)

        el.addEventListener('mouseenter', () => clearTimeout(timer))
        el.addEventListener('mouseleave', () => {
            timer = setTimeout(() => bootstrap.Alert.getOrCreateInstance(el).close(), delay)
        })
    })

    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el))

    const colorpicker = document.querySelector('.colorpicker')
    const colorpickerAddon = document.querySelector('.colorpicker-addon')
    if (colorpicker && colorpickerAddon) {
        colorpicker.addEventListener('input', () => colorpickerAddon.value = colorpicker.value)
        colorpickerAddon.addEventListener('input', () => colorpicker.value = colorpickerAddon.value)
    }

    document.querySelectorAll('.phone').forEach(el => applyMask(el, '+0 000 000-00-00-00'))
    document.querySelectorAll('.birthday').forEach(el => applyMask(el, '00.00.0000'))

    const scrollupBtn = document.querySelector('.scrollup')
    if (scrollupBtn) {
        window.addEventListener('scroll', function () {
            const visible = window.scrollY > 200
            scrollupBtn.style.opacity = visible ? '1' : '0'
            scrollupBtn.style.pointerEvents = visible ? 'auto' : 'none'
        })
        scrollupBtn.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' })
            return false
        })
    }

    document.querySelector('.js-messages-block')?.addEventListener('show.bs.dropdown', function () {
        getNewMessages()
    })

    function setTheme(theme) {
        document.documentElement.setAttribute('data-bs-theme', theme)
        const icon = theme === 'dark' ? 'fa-moon' : 'fa-sun'
        const themeIcon = document.getElementById('theme-icon-active')
        if (themeIcon) themeIcon.className = `fa-regular ${icon} fa-lg`
        ajax({ type: 'POST', url: '/ajax/set-theme', data: { theme } })
    }

    document.querySelectorAll('[data-bs-theme-value]').forEach(el => {
        el.addEventListener('click', () => setTheme(el.dataset.bsThemeValue))
    })

    if (window.location.hash) {
        const initialHash = window.location.hash
        if (initialHash === '#comments') {
            history.replaceState(null, '', location.pathname + location.search)
        }
        setTimeout(function () {
            const target = document.querySelector(initialHash)
            if (target) scrollToElement(target, 'instant')
        }, 100)
    } else if (new URLSearchParams(location.search).has('page')) {
        const commentsEl = document.querySelector('#comments')
        if (commentsEl) {
            setTimeout(() => scrollToElement(commentsEl, 'instant'), 100)
        }
    }

    initShortView()

    prettyPrint()

    tags.init('.input-tag', {
        allowNew: true,
        server: '/blogs/tags-search',
        liveServer: true,
        clearEnd: true,
        allowClear: true,
        suggestionsThreshold: 2,
        max: 10,
        separator: [','],
        addOnBlur: true,
    })

    // Выбор пользователей, адрес поиска задается через data-server
    tags.init('.input-user', {
        allowNew: false,
        liveServer: true,
        clearEnd: true,
        allowClear: true,
        suggestionsThreshold: 2,
        max: 10,
        // Ищем и по логину, и по имени, иначе список фильтруется только по логину
        searchFields: ['login', 'name'],
        // В подсказках показываем «логин — имя», как в упоминаниях tiptap
        onRenderItem: (item, label, inst) => {
            const text = item.name && item.name !== item.login ? `${item.login} — ${item.name}` : item.login

            return inst.config('sanitizer')(text)
        },
    })

    fancybox.bind('[data-fancybox]:not(.fancybox-exclude)', {})

    let hoveredCarousel = null
    document.querySelectorAll('.f-carousel').forEach(el => {
        const carousel = fancyCarousel(el, { infinite: true, adaptiveHeight: true }, fancyCarouselPlugins)
        carousel.init()

        el.addEventListener('mouseenter', () => hoveredCarousel = carousel)
        el.addEventListener('mouseleave', () => { if (hoveredCarousel === carousel) hoveredCarousel = null })
    })

    document.addEventListener('keydown', e => {
        if (!hoveredCarousel) return
        if (e.key === 'ArrowLeft') {
            e.preventDefault()
            hoveredCarousel.prev()
        } else if (e.key === 'ArrowRight') {
            e.preventDefault()
            hoveredCarousel.next()
        }
    })

    document.querySelectorAll('.slide-thumb-link[data-type="html5video"]').forEach(link => {
        const video = link.querySelector('video')
        if (!video) return

        const setThumb = () => {
            const canvas = document.createElement('canvas')
            canvas.width = video.videoWidth || 160
            canvas.height = video.videoHeight || 90
            canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height)
            link.dataset.thumb = canvas.toDataURL('image/jpeg', 0.8)
        }

        video.readyState >= 2 ? setThumb() : video.addEventListener('loadeddata', setThumb, { once: true })
    })
})

/* Карточка целиком ведёт по адресу: data-href на блоке.
 * Ссылки и кнопки внутри работают как обычно */
document.addEventListener('click', function (e) {
    const block = e.target.closest('[data-href]')

    if (block && ! e.target.closest('a, button, input, label')) {
        window.location = block.dataset.href
    }
})

/* Раскрытие скрытого блока по ссылке: data-reveal — что показать,
 * data-reveal-hide — что убрать (обычно саму ссылку с обёрткой) */
document.addEventListener('click', function (e) {
    const el = e.target.closest('[data-reveal]')
    if (!el) return

    e.preventDefault()

    const target = document.querySelector(el.dataset.reveal)
    if (target) target.style.display = 'block'

    const hide = el.dataset.revealHide ? document.querySelector(el.dataset.revealHide) : null
    if (hide) hide.style.display = 'none'
})

/* Переход к форме ввода */
window.postJump = function () {
    const form = document.querySelector('.section-form')
    if (form) scrollToElement(form)
}

/* Сворачивание/разворачивание */
window.toggleComment = function (id) {
    const body = document.getElementById('comment-body-' + id)
    const expandLabel = document.getElementById('comment-expand-' + id)
    const ctrl = document.getElementById('comment-ctrl-' + id)
    if (!body) return

    const isHidden = body.classList.toggle('d-none')

    if (expandLabel) expandLabel.classList.toggle('d-none', !isHidden)

    if (ctrl) {
        const icon = ctrl.querySelector('i')
        if (icon) icon.className = isHidden ? 'fa fa-plus' : 'fa fa-minus'
        const line = ctrl.querySelector('.comment-thread-line')
        if (line) line.classList.toggle('d-none', isHidden)
    }
}

/* Редактор для поля, созданного после загрузки страницы: tiptap подключается
 * чанком по требованию, повторный вызов отдаёт уже готовый экземпляр */
async function ensureEditor(textarea) {
    const id = textarea?.id
    if (!id) return null

    if (!window._tiptapEditors?.[id]) {
        textarea.classList.add('tiptap')
        const { initEditors } = await import('./tiptap.js')
        initEditors([textarea])
    }

    return window._tiptapEditors?.[id] ?? null
}

/* Открыть форму ответа под комментарием */
window.openReplyForm = function (id, callback) {
    document.querySelectorAll('.reply-form').forEach(function (f) {
        f.classList.add('d-none')
    })
    const form = document.getElementById('reply-form-' + id)
    if (!form) return false

    form.classList.remove('d-none')

    ensureEditor(form.querySelector('textarea')).then(editor => {
        editor?.commands.focus()
        callback?.(editor)
    })

    return false
}

/* Закрыть форму ответа */
window.closeReplyForm = function (id) {
    document.getElementById('reply-form-' + id)?.classList.add('d-none')
}

/* Тогл панели форматирования в форме ответа */
window.toggleReplyToolbar = function (btn) {
    btn.closest('.reply-form').classList.toggle('toolbar-visible')
}

/* AJAX отправка формы ответа на комментарий */
document.addEventListener('submit', function (e) {
    const form = e.target.closest('.reply-form form')
    if (!form) return

    e.preventDefault()

    const errorEl = form.querySelector('.reply-error')
    if (errorEl) errorEl.textContent = ''

    const submitBtn = form.querySelector('button[type="submit"], button:not([type="button"])')
    if (submitBtn) submitBtn.disabled = true

    ajax({
        url: form.action, type: 'POST', data: new FormData(form),
        // Ответ приходит адресом записи при успехе и списком ошибок при отказе
        success: function (data) {
            if (data.redirect) {
                window.location.hash = data.redirect.includes('#') ? data.redirect.split('#')[1] : ''
                window.location.reload()

                return
            }

            if (errorEl) errorEl.textContent = Object.values(data.errors || {}).flat().join(', ')
            if (submitBtn) submitBtn.disabled = false
        },
        error: function () {
            if (submitBtn) submitBtn.disabled = false
        },
    })
})

/* Переключение языка (ajax, без перезагрузки на /language) */
document.addEventListener('click', function (e) {
    const el = e.target.closest('[data-lang]')
    if (!el) return
    e.preventDefault()
    ajax({
        url: '/language/' + el.dataset.lang,
        type: 'POST',
        success: () => location.reload(),
    })
})

/* Ответ на сообщение (для форумов/стен без вложенных комментариев) */
window.postReply = function (el) {
    postJump()

    const authorEl = el.closest('.section')?.querySelector('.section-author')
    const author = authorEl?.dataset.login || authorEl?.textContent.trim()
    if (!author) return false

    const editor = window._tiptapActiveEditor
    if (!editor) return false

    if (authorEl.matches('a')) {
        editor.chain().focus('end', { scrollIntoView: false }).insertContent([
            { type: 'mention', attrs: { id: author, label: author } },
            { type: 'text', text: ' ' },
        ]).run()
    } else {
        editor.chain().focus('end', { scrollIntoView: false }).insertContent({ type: 'text', text: author + ', ' }).run()
    }

    return false
}

/* Цитирование сообщения */
function doInsertQuote (editor, authorEl, author, date, message) {
    if (!message) {
        if (author) {
            editor.chain().focus('end', { scrollIntoView: false }).insertContent([
                { type: 'mention', attrs: { id: author, label: author } },
                { type: 'text', text: ' ' },
            ]).run()
        }
        return
    }

    const quoteContent = [
        {
            type: 'blockquote',
            attrs: { author: author ? (authorEl.matches('a') ? '@' : '') + author + (date ? ' ' + date : '') : (date || null) },
            content: [{ type: 'paragraph', content: [{ type: 'text', text: message }] }],
        },
        { type: 'paragraph' },
    ]

    if (editor.isEmpty) {
        editor.chain().focus('end', { scrollIntoView: false }).setContent({ type: 'doc', content: quoteContent }).run()
    } else {
        const doc = editor.state.doc
        const lastChild = doc.lastChild
        const insertPos = (lastChild && lastChild.type.name === 'paragraph' && lastChild.childCount === 0)
            ? doc.content.size - lastChild.nodeSize
            : doc.content.size
        editor.chain().focus('end', { scrollIntoView: false }).insertContentAt(insertPos, quoteContent).run()
    }
}

/* Автор, дата и текст записи для цитаты; вложенные цитаты в текст не попадают */
function extractQuote(root) {
    const authorEl = root?.querySelector('.section-author')
    const dateEl   = root?.querySelector('.section-date')
    const clone    = root?.querySelector('.section-message')?.cloneNode(true)
    clone?.querySelectorAll('blockquote').forEach(bq => bq.remove())

    return {
        authorEl,
        author:  authorEl?.dataset.login || authorEl?.textContent.trim() || null,
        date:    (dateEl?.dataset.date || dateEl?.textContent || '').trim(),
        message: clone?.textContent.trim() || '',
    }
}

window.postQuote = function (el) {
    const commentItem = el.closest('.comment-item')

    if (commentItem) {
        const { authorEl, author, date, message } = extractQuote(el.closest('.comment-right'))

        openReplyForm(commentItem.dataset.id, function (editor) {
            if (editor) doInsertQuote(editor, authorEl, author, date, message)
        })
        return false
    }

    postJump()

    const editor = window._tiptapActiveEditor
    if (!editor) return false

    const { authorEl, author, date, message } = extractQuote(el.closest('.section'))
    doInsertQuote(editor, authorEl, author, date, message)
    return false
}

/* Редактирование комментария в модальном окне */
window.openEditModal = function (el) {
    const id      = el.dataset.id
    const baseUrl = el.dataset.url
    const modalEl = document.getElementById('editCommentModal')

    document.getElementById('edit-comment-id').value = id
    modalEl.dataset.editUrl = baseUrl + '/' + id

    modalEl.querySelector('input[type="file"]')?.setAttribute('data-id', id)
    const msgEl = document.getElementById('edit-comment-msg')
    if (msgEl) msgEl.dataset.relateId = id

    const filesContainer = modalEl.querySelector('.js-files')
    if (filesContainer) filesContainer.innerHTML = ''

    const dataPromise = new Promise(resolve => {
        ajax({
            url: baseUrl + '/' + id,
            success: (data) => {
                const scope = modalEl.querySelector('form')

                data.files?.forEach(file => renderFile(scope, filesContainer, file))

                resolve(data.text || '')
            },
            error: () => resolve(''),
        })
    })

    const onShown = async () => {
        modalEl.removeEventListener('shown.bs.modal', onShown)

        const text = await dataPromise
        const created = !window._tiptapEditors?.['edit-comment-msg']
        const editor = await ensureEditor(msgEl)

        // Свежему редактору нужен кадр на вёрстку, иначе setContent не отрисуется
        if (created) await new Promise(resolve => requestAnimationFrame(resolve))

        if (editor) {
            editor.commands.setContent(text, true)
            editor.resetChanged()
        }
    }

    modalEl.addEventListener('shown.bs.modal', onShown)
    bootstrap.Modal.getOrCreateInstance(modalEl).show()

    return false
}

document.getElementById('editCommentModal')?.addEventListener('hide.bs.modal', function (e) {
    const editor = window._tiptapEditors?.['edit-comment-msg']
    if (editor?.getIsChanged()) {
        // Закрытие откладывается: ответ диалога придёт колбэком
        e.preventDefault()

        confirm(__('confirm_discard_changes'), (result) => {
            if (!result) return
            editor.resetChanged()
            bootstrap.Modal.getInstance(this)?.hide()
        })
    }
})

document.getElementById('editCommentForm')?.addEventListener('submit', function (e) {
    e.preventDefault()
    const modalEl = document.getElementById('editCommentModal')
    const id      = document.getElementById('edit-comment-id').value
    const msg     = document.getElementById('edit-comment-msg').value
    const url     = modalEl?.dataset.editUrl

    ajax({
        data: { msg },
        dataType: 'json', type: 'patch', url,
        success: function (data) {
            if (data.success) {
                bootstrap.Modal.getInstance(modalEl)?.hide()
                window.location.hash = '#comment_' + id
                window.location.reload()
            } else {
                notyf.error(data.message)
            }
        },
        error: () => notyf.error(__('request_failed')),
    })
})

/* Копирует текст в буфер обмена */
window.copyToClipboard = function (el) {
    const container = el.closest('.input-group') ?? el.parentElement
    const field = container?.querySelector('input, textarea')
    const text = el.dataset.copy ?? field?.value ?? ''

    const fallback = () => {
        if (field) { field.select(); document.execCommand('copy'); return }
        if (!text) return

        const ta = document.createElement('textarea')
        ta.value = text
        ta.style.position = 'fixed'
        ta.style.opacity = '0'
        document.body.appendChild(ta)
        ta.select()
        try { document.execCommand('copy') } catch (e) {}
        document.body.removeChild(ta)
    }

    if (navigator.clipboard?.writeText && text) {
        navigator.clipboard.writeText(text).catch(fallback)
    } else {
        fallback()
    }

    // Галочка на иконке триггера
    const icon = el.querySelector('i')
    if (icon && !icon.dataset.copyReset) {
        const prev = icon.className
        icon.dataset.copyReset = '1'
        icon.className = 'fas fa-check'
        setTimeout(() => {
            icon.className = prev
            delete icon.dataset.copyReset
        }, 1500)
    }

    // Подсказка «скопировано»: на .input-group-text либо на самом триггере
    const tooltipEl = container?.querySelector('.input-group-text')
        ?? (el.matches('[data-bs-toggle="tooltip"]') ? el : null)

    if (tooltipEl) {
        const original = tooltipEl.getAttribute('data-bs-original-title') ?? tooltipEl.getAttribute('title')
        const tip = bootstrap.Tooltip.getOrCreateInstance(tooltipEl)
        tooltipEl.setAttribute('data-bs-original-title', __('copied'))
        tip.update()
        tip.show()

        if (original !== null) {
            setTimeout(() => {
                tooltipEl.setAttribute('data-bs-original-title', original)
                tip.update()
            }, 1500)
        }
    }

    return false
}

/* Загрузка файла */
window.submitFile = function (el) {
    const form = new FormData()
    form.append('file', el.files[0])
    form.append('id', el.dataset.id)
    form.append('type', el.dataset.type)

    const scope = el.closest('form') ?? document
    const filesContainer = scope.querySelector('.js-files')

    ajax({
        data: form, type: 'post', dataType: 'json', url: '/ajax/file/upload',
        beforeSend: () => filesContainer?.insertAdjacentHTML('beforeend', '<i class="fas fa-spinner fa-spin fa-3x mx-3"></i>'),
        complete: () => filesContainer?.querySelectorAll('.fa-spinner').forEach(s => s.remove()),
        success: function (data) {
            if (!data.success) { notyf.error(data.message); return }

            renderFile(scope, filesContainer, data)
        },
        error: (_, textStatus) => notyf.error(__('file_upload_failed') + ' ' + textStatus)
    })

    el.value = ''
    return false
}

/* Удаление медиафайла (изображения или видео) из редактора */
window.cutMedia = function (path) {
    if (!path) return

    const editor = window._tiptapActiveEditor
    if (!editor) return

    const normalize = (src) => { try { return new URL(src).pathname } catch { return src } }
    const normalizedPath = normalize(path)

    const { state, dispatch } = editor.view
    const tr = state.tr
    const positions = []

    state.doc.descendants(function (node, pos) {
        const src = node.attrs.src ?? node.attrs.href
        if (['image', 'video'].includes(node.type.name) && normalize(src) === normalizedPath) {
            positions.push({ pos, size: node.nodeSize })
        }
    })

    positions.reverse().forEach(({ pos, size }) => tr.delete(pos, pos + size))

    // noinspection JSUnresolvedReference
    if (tr.docChanged) dispatch(tr)
}

/* Удаление файла */
window.deleteFile = function (el) {
    confirm(__('confirm_file_delete'), function (result) {
        if (!result) return

        ajax({
            url: '/ajax/file/delete', type: 'POST', dataType: 'json',
            data: { id: el.dataset.id, type: el.dataset.type },
            success: function (data) {
                if (!data.success) { notyf.error(data.message); return }
                if (data.path) cutMedia(data.path)
                el.closest('.js-file').style.display = 'none'
            },
            error: (_, textStatus) => notyf.error(__('file_delete_failed') + ' ' + textStatus)
        })
    })

    return false
}

/* Update message count */
window.updateMessageCount = function (newCount) {
    const count = parseInt(newCount) || 0

    document.querySelectorAll('.js-message-count').forEach(el => el.textContent = count || '')
}

/* Get new messages */
let newMessagesLoading = false
window.getNewMessages = function () {
    if (newMessagesLoading) return false
    newMessagesLoading = true

    const messagesList = document.querySelector('.js-messages-block .js-messages')

    ajax({
        dataType: 'json', type: 'GET', url: '/messages/new',
        beforeSend: () => messagesList?.insertAdjacentHTML('beforeend', '<li class="js-message-spin text-center"><i class="fas fa-spinner fa-spin fa-2x my-2"></i></li>'),
        complete: () => {
            newMessagesLoading = false
            messagesList?.querySelectorAll('.js-message-spin').forEach(s => s.remove())
        },
        success(data) {
            if (!data?.success) {
                updateMessageCount(0)
                return
            }

            updateMessageCount(data.countMessages)

            if (messagesList) {
                messagesList.innerHTML = ''
                messagesList.insertAdjacentHTML('beforeend', data.dialogues)
            }
        }
    })

    return false
}

/* Инициализирует главное изображение слайдера */
window.initSlideMainImage = function (el) {
    const mainHref = el.getAttribute('href')
    const slider = el.closest('.media-file')

    slider?.querySelectorAll('.slide-thumb-link').forEach(l => l.classList.remove('fancybox-exclude'))
    slider?.querySelectorAll(`.slide-thumb-link[href="${mainHref}"]`).forEach(l => l.classList.add('fancybox-exclude'))
}

/* Инициализирует миниатюру слайдера */
window.initSlideThumbImage = function (el) {
    const href = el.getAttribute('href')
    const isVideo = el.dataset.type === 'html5video'
    const fancyboxGroup = el.dataset.fancybox ?? ''
    const slider = el.closest('.media-file')
    const mainInner = slider?.querySelector('.slide-main-inner')

    if (!mainInner) return false

    if (el.querySelector('.slide-thumb-image, .slide-thumb-video')?.classList.contains('active')) return false

    mainInner.querySelector('video')?.pause()

    if (isVideo) {
        mainInner.innerHTML = `<video src="${href}" class="img-fluid rounded" controls preload="metadata"></video>`
    } else {
        const alt = (el.querySelector('img')?.getAttribute('alt') ?? '').replace(/"/g, '&quot;')
        mainInner.innerHTML =
            `<a href="${href}" class="slide-main-link" data-fancybox="${fancyboxGroup}" onclick="return initSlideMainImage(this)">` +
            `<img src="${href}" alt="${alt}" class="img-fluid rounded slide-main-img">` +
            `</a>`
    }

    slider?.querySelectorAll('.slide-thumb-image, .slide-thumb-video').forEach(t => t.classList.remove('active'))
    el.querySelector('.slide-thumb-image, .slide-thumb-video')?.classList.add('active')

    return false
}

let checkTimeout
/* Проверка логина */
window.checkLogin = function (el) {
    const block = el.closest('.mb-3')
    const message = block?.querySelector('.invalid-feedback')
    const login = el.value.trim()

    if (login.length < 3) {
        block?.classList.remove('is-valid', 'is-invalid')
        if (message) message.textContent = ''
        return
    }

    clearTimeout(checkTimeout)

    checkTimeout = setTimeout(function () {
        ajax({
            url: '/check-login', type: 'POST', dataType: 'json',
            data: { login },
            success: (data) => {
                block?.classList.toggle('is-valid', data.success)
                block?.classList.toggle('is-invalid', !data.success)
                if (message) message.textContent = data.success ? '' : data.message
            },
            error: () => {
                block?.classList.remove('is-valid')
                block?.classList.add('is-invalid')
            }
        })
    }, 1000)

    return false
}

// Кнопка "Загрузить ещё" для ленты
const feedContainer = document.getElementById('feed-container')
const feedSentinel  = document.getElementById('feed-sentinel')

if (feedContainer && feedSentinel) {
    let loading = false

    const getNextUrl = () => {
        const items = feedContainer.querySelectorAll('.feed-pagination')
        return items[items.length - 1]?.dataset.next || ''
    }

    const loader = document.createElement('div')
    loader.className = 'feed-loader d-none'
    loader.innerHTML = '<span></span><span></span><span></span><span></span><span></span>'
    feedSentinel.before(loader)

    const createLoadMoreButton = () => {
        const btn = document.createElement('button')
        btn.type = 'button'
        btn.className = 'btn btn-primary d-block mx-auto my-3'
        btn.textContent = __('buttons.load_more')
        btn.addEventListener('click', () => {
            const nextUrl = getNextUrl()
            if (!nextUrl || loading) return

            loading = true
            btn.remove()
            loader.classList.remove('d-none')

            ajax({
                url: nextUrl, dataType: 'text',
                complete: () => {
                    loading = false
                    loader.classList.add('d-none')
                },
                success: (html) => {
                    const temp = document.createElement('div')
                    temp.innerHTML = html

                    // Пустая страница приходит с признаком в пагинации: кнопку не возвращаем
                    if (temp.querySelector('.feed-pagination')?.dataset.empty === '1') {
                        return
                    }

                    feedContainer.append(...temp.children)
                    initShortView(feedContainer)

                    if (getNextUrl()) loader.before(createLoadMoreButton())
                },
            })
        })
        return btn
    }

    if (getNextUrl()) loader.before(createLoadMoreButton())
}
