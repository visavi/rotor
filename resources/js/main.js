import * as bootstrap from 'bootstrap'
import { __ } from './translate.js'
import './globals.js'
import './tiptap-editor.js'
import './prettify.js'

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content

function getNavbarHeight() {
    let max = 0
    document.querySelectorAll('.app-header, .app-topnav').forEach(el => {
        max = Math.max(max, el.getBoundingClientRect().bottom)
    })
    if (!max) {
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

function initShortView(container = document) {
    container.querySelectorAll('.section-content.short-view:not(.clamped):not(.expanded)').forEach(function (el) {
        const hiddenPixels = el.scrollHeight - el.clientHeight
        if (hiddenPixels > 100) {
            el.classList.add('clamped')
            const btn = document.createElement('button')
            btn.type = 'button'
            btn.className = 'btn btn-sm btn-adaptive mt-2'
            btn.textContent = 'Показать полностью'
            btn.addEventListener('click', function () {
                el.classList.add('expanded')
                el.classList.remove('clamped')
                btn.remove()
            })
            el.after(btn)
        } else if (hiddenPixels > 0) {
            el.classList.remove('short-view')
        }
    })
}

function ajax({ url, type = 'GET', data = null, dataType = 'json', beforeSend, complete, success, error }) {
    if (beforeSend) beforeSend()

    const options = {
        method: type.toUpperCase(),
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
        }
    }

    if (data) {
        if (data instanceof FormData) {
            options.body = data
        } else {
            options.headers['Content-Type'] = 'application/x-www-form-urlencoded'
            options.body = new URLSearchParams(data)
        }
    }

    fetch(url, options)
        .then(res => dataType === 'json' ? res.json() : res.text())
        .then(responseData => { if (success) success(responseData) })
        .catch(err => { if (error) error(null, err.message, err) })
        .finally(() => { if (complete) complete() })
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

    const popovers = document.querySelectorAll('[data-bs-toggle="popover"]')
    popovers.forEach(el => new bootstrap.Popover(el))

    document.body.addEventListener('click', function (e) {
        if (!e.target.closest('[data-bs-toggle="popover"]') && !e.target.closest('.popover')) {
            popovers.forEach(el => bootstrap.Popover.getInstance(el)?.hide())
        }
    })

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
            if (target) {
                const navbarHeight = getNavbarHeight()
                window.scrollTo({ top: target.getBoundingClientRect().top + window.scrollY - navbarHeight, behavior: 'instant' })
            }
        }, 100)
    } else if (new URLSearchParams(location.search).has('page')) {
        const commentsEl = document.querySelector('#comments')
        if (commentsEl) {
            setTimeout(function () {
                const navbarHeight = getNavbarHeight()
                window.scrollTo({ top: commentsEl.getBoundingClientRect().top + window.scrollY - navbarHeight, behavior: 'instant' })
            }, 100)
        }
    }

    setTimeout(initShortView, 300)

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

/* Показ формы загрузки файла */
window.showAttachForm = function () {
    const btn = document.querySelector('.js-attach-button')
    const form = document.querySelector('.js-attach-form')
    if (btn) btn.style.display = 'none'
    if (form) form.style.display = 'block'
    return false
}

/* Переход к форме ввода */
window.postJump = function () {
    const form = document.querySelector('.section-form')
    if (form) {
        const navbarHeight = getNavbarHeight()
        window.scrollTo({ top: form.getBoundingClientRect().top + window.scrollY - navbarHeight, behavior: 'smooth' })
    }
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

/* Открыть форму ответа под комментарием */
window.openReplyForm = function (id, callback) {
    document.querySelectorAll('.reply-form').forEach(function (f) {
        f.classList.add('d-none')
    })
    const form = document.getElementById('reply-form-' + id)
    if (!form) return false

    form.classList.remove('d-none')

    const textarea = form.querySelector('textarea')
    const editorId = textarea?.id

    if (textarea && editorId && !window._tiptapEditors?.[editorId]) {
        textarea.classList.add('tiptap')
        import('./tiptap.js').then(({ initEditors }) => {
            initEditors([textarea])
            const ed = window._tiptapEditors?.[editorId]
            ed?.commands.focus()
            callback?.(ed)
        })
    } else {
        const ed = window._tiptapEditors?.[editorId]
        ed?.commands.focus()
        callback?.(ed)
    }

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

    fetch(form.action, {
        method: 'POST',
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: new FormData(form),
    })
        .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d } }) })
        .then(function ({ ok, data }) {
            if (ok) {
                const hash = data.redirect.includes('#') ? data.redirect.split('#')[1] : ''
                window.location.hash = hash
                window.location.reload()
            } else {
                const msg = Object.values(data.errors || {}).flat().join(', ')
                if (errorEl) errorEl.textContent = msg
                if (submitBtn) submitBtn.disabled = false
            }
        })
        .catch(function () {
            if (submitBtn) submitBtn.disabled = false
        })
})

/* Схлопывание/разворачивание ветки комментариев */
document.addEventListener('click', function (e) {
    const btn = e.target.closest('.comment-collapse-btn')
    if (!btn) return
    const id = btn.dataset.id
    const children = document.getElementById('comment-children-' + id)
    if (!children) return
    const icon = btn.querySelector('i')
    const collapsed = children.classList.toggle('d-none')
    icon.className = collapsed ? 'fa fa-plus text-muted' : 'fa fa-minus text-muted'
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

window.postQuote = function (el) {
    const commentItem = el.closest('.comment-item')

    if (commentItem) {
        const id       = commentItem.dataset.id
        const rcRight  = el.closest('.comment-right')
        const authorEl = rcRight?.querySelector('.section-author')
        const author   = authorEl?.dataset.login || authorEl?.textContent.trim() || null
        const dateEl   = rcRight?.querySelector('.section-date')
        const date     = (dateEl?.dataset.date || dateEl?.textContent || '').trim()
        const clone    = rcRight?.querySelector('.section-message')?.cloneNode(true)
        clone?.querySelectorAll('blockquote').forEach(bq => bq.remove())
        const message  = clone?.textContent.trim() || ''

        openReplyForm(id, function (editor) {
            if (editor) doInsertQuote(editor, authorEl, author, date, message)
        })
        return false
    }

    postJump()

    const post     = el.closest('.section')
    const authorEl = post?.querySelector('.section-author')
    const author   = authorEl?.dataset.login || authorEl?.textContent.trim() || null
    const dateEl   = post?.querySelector('.section-date')
    const date     = (dateEl?.dataset.date || dateEl?.textContent || '').trim()
    const clone    = post?.querySelector('.section-message')?.cloneNode(true)
    const editor   = window._tiptapActiveEditor

    if (!editor) return false

    clone?.querySelectorAll('blockquote').forEach(bq => bq.remove())
    const message = clone?.textContent.trim() || ''

    doInsertQuote(editor, authorEl, author, date, message)
    return false
}

/* Подтверждение действия */
const confirmedElements = new WeakSet()

window.confirmAction = function (el) {
    const message = el.dataset.confirm || 'Вы уверены?'

    if (confirmedElements.has(el)) {
        confirmedElements.delete(el)
        return true
    }

    confirm(message, function (result) {
        if (!result) return
        const form = el.matches('form') ? el : el.closest('form')
        if (form) {
            confirmedElements.add(form)
            form.submit()
        } else {
            const href = el.getAttribute('href')
            if (href) window.location.href = href
        }
    })

    return false
}

/* Отправка жалобы на спам */
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

    const dataPromise = fetch(baseUrl + '/' + id, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken }
    }).then(r => r.json()).then(data => {
        const scope = modalEl.querySelector('form')
        data.files?.forEach(file => {
            const templateEl = scope?.querySelector(file.isImage ? '.js-image-template' : '.js-file-template')
            const template = templateEl?.cloneNode(true)
            if (!template) return
            if (file.isImage) {
                template.querySelector('img')?.setAttribute('src', file.path)
            } else {
                const link = template.querySelector('.js-file-link')
                if (link) { link.href = file.path; link.textContent = file.name }
                const sizeEl = template.querySelector('.js-file-size')
                if (sizeEl) sizeEl.textContent = file.size
            }
            template.querySelector('.js-file-delete')?.setAttribute('data-id', file.id)
            filesContainer?.insertAdjacentHTML('beforeend', template.innerHTML)
        })
        return data.text || ''
    })

    const onShown = async () => {
        modalEl.removeEventListener('shown.bs.modal', onShown)

        const text = await dataPromise

        if (!window._tiptapEditors?.['edit-comment-msg'] && msgEl) {
            msgEl.classList.add('tiptap')
            const { initEditors } = await import('./tiptap.js')
            initEditors([msgEl])
            await new Promise(resolve => requestAnimationFrame(resolve))
        }

        const editor = window._tiptapEditors?.['edit-comment-msg']
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
        e.preventDefault()
        if (window.confirm(__('confirm_discard_changes'))) {
            editor.resetChanged()
            bootstrap.Modal.getInstance(this)?.hide()
        }
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
        }
    })
})

/* Удаление комментариев */
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

            const isMedia = data.type === 'image' || data.type === 'video'
            const templateEl = scope.querySelector(isMedia ? '.js-image-template' : '.js-file-template')
            const template = templateEl?.cloneNode(true)

            if (data.type === 'image') {
                template?.querySelector('img')?.setAttribute('src', data.path)
            } else if (data.type === 'video') {
                const img = template?.querySelector('img')
                if (img) {
                    const wrap = img.parentElement
                    const video = document.createElement('video')
                    video.src = data.path
                    video.className = img.className
                    video.preload = 'metadata'
                    img.replaceWith(video)
                    wrap?.insertAdjacentHTML('beforeend', '<span class="slide-play-icon">▶</span>')
                }
            } else {
                const link = template?.querySelector('.js-file-link')
                if (link) { link.href = data.path; link.textContent = data.name }
                const sizeEl = template?.querySelector('.js-file-size')
                if (sizeEl) sizeEl.textContent = data.size
            }

            template?.querySelector('a')?.setAttribute('data-id', data.id)
            if (template) filesContainer?.insertAdjacentHTML('beforeend', template.innerHTML)
        },
        error: (_, textStatus) => notyf.error('Ошибка загрузки файла: ' + textStatus)
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
            error: (_, textStatus) => notyf.error('Ошибка удаления файла: ' + textStatus)
        })
    })

    return false
}

/* Показывает форму для повторной отправки кода подтверждения */
window.resendingCode = function () {
    const link = document.querySelector('.js-resending-link')
    const form = document.querySelector('.js-resending-form')
    if (link) link.style.display = 'none'
    if (form) form.style.display = 'block'
    return false
}

/* Показывает панель с запросами */
window.showQueries = function () {
    const el = document.querySelector('.js-queries')
    if (!el) return
    el.style.display = getComputedStyle(el).display === 'none' ? '' : 'none'
}

/* Update message count */
window.updateMessageCount = function (newCount) {
    const data = JSON.parse(localStorage.getItem('messageData') || '{}')
    data.countMessages = parseInt(newCount) || 0
    localStorage.setItem('messageData', JSON.stringify(data))
    localStorage.setItem('messageCount', newCount)
    window.dispatchEvent(new Event('storage'))
}

/* Get new messages */
let newMessagesLoading = false
window.getNewMessages = function () {
    if (newMessagesLoading) return false
    newMessagesLoading = true

    const notifyItem = document.querySelector('.js-messages-block .app-nav__item')
    const badge = notifyItem?.querySelector('.badge')
    const titleSpan = document.querySelector('.app-notification__title span')
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
                badge?.remove()
                if (titleSpan) titleSpan.textContent = 0
                return
            }

            const count = data.countMessages

            if (badge) {
                badge.textContent = count
            } else if (notifyItem) {
                const newBadge = document.createElement('span')
                newBadge.className = 'badge bg-notify'
                newBadge.textContent = count
                notifyItem.append(newBadge)
            }

            updateMessageCount(count)

            if (titleSpan) titleSpan.textContent = count
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

    slider?.querySelectorAll('.slide-thumb-image').forEach(img => img.classList.remove('active'))
    slider?.querySelectorAll('.slide-thumb-video').forEach(v => v.classList.remove('active'))

    const thumb = el.querySelector('.slide-thumb-image, .slide-thumb-video')
    thumb?.classList.add('active')

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

const confirmDialogEl = document.createElement('dialog')
confirmDialogEl.className = 'confirm-dialog'
confirmDialogEl.innerHTML = `
<p class="confirm-message"></p>
<div class="confirm-footer">
    <button type="button" class="btn btn-secondary btn-sm js-confirm-cancel"></button>
    <button type="button" class="btn btn-primary btn-sm js-confirm-ok"></button>
</div>`
document.body.appendChild(confirmDialogEl)

function confirm(message, callback) {
    confirmDialogEl.querySelector('.confirm-message').textContent = message
    confirmDialogEl.querySelector('.js-confirm-ok').textContent = __('buttons.ok')
    confirmDialogEl.querySelector('.js-confirm-cancel').textContent = __('buttons.cancel')
    confirmDialogEl.querySelector('.js-confirm-ok').onclick = () => { confirmDialogEl.close(); callback(true) }
    confirmDialogEl.querySelector('.js-confirm-cancel').onclick = () => { confirmDialogEl.close(); callback(false) }
    confirmDialogEl.showModal()
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
        btn.addEventListener('click', async () => {
            const nextUrl = getNextUrl()
            if (!nextUrl || loading) return

            loading = true
            btn.remove()
            loader.classList.remove('d-none')

            try {
                const response = await fetch(nextUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                const html     = await response.text()
                const temp     = document.createElement('div')
                temp.innerHTML = html

                if (temp.querySelector('.feed-pagination')?.dataset.empty === '1') return

                feedContainer.append(...temp.children)
                setTimeout(initShortView, 100)

                if (getNextUrl()) loader.before(createLoadMoreButton())
            } finally {
                loading = false
                loader.classList.add('d-none')
            }
        })
        return btn
    }

    if (getNextUrl()) loader.before(createLoadMoreButton())
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

function ajaxPayload(el) {
    if (el.matches('form')) return new FormData(el)

    const data = {}
    for (const [key, value] of Object.entries(el.dataset)) {
        if (!ajaxReserved.includes(key)) data[key] = value
    }

    return data
}

function ajaxSend(el) {
    const url = el.dataset.ajaxUrl || el.getAttribute('action') || el.getAttribute('href')

    // Пока запрос в пути, повторные клики игнорируются
    if (!url || el.dataset.ajaxLoading) return

    const method = el.dataset.ajaxMethod || (el.matches('form') ? el.method : 'post')
    // Данные собираются до блокировки: отключённые поля в FormData не попадают
    const data = ajaxPayload(el)
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
        ajaxSend(el)
        return
    }

    // Пустой data-ajax-confirm — спросить обычным текстом про удаление записи
    const message = el.dataset.ajaxConfirm || __('confirm_message_delete')

    confirm(message, (result) => { if (result) ajaxSend(el) })
}

document.addEventListener('submit', function (event) {
    const form = event.target.closest('form[data-ajax]')
    if (form) ajaxHandle(form, event)
})

document.addEventListener('click', function (event) {
    const el = event.target.closest('[data-ajax]:not(form)')
    if (el) ajaxHandle(el, event)
})
