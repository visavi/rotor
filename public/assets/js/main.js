import * as bootstrap from 'bootstrap'
import { trans as __ } from './translate.js'
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
                const behavior = initialHash === '#comments' ? 'instant' : 'smooth'
                window.scrollTo({ top: target.getBoundingClientRect().top + window.scrollY - navbarHeight, behavior })
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

    fancybox.bind('[data-fancybox]:not(.fancybox-exclude)', {})
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

/* Ответ на сообщение */
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
window.postQuote = function (el) {
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

    if (!message) {
        if (author) {
            editor.chain().focus('end', { scrollIntoView: false }).insertContent([
                { type: 'mention', attrs: { id: author, label: author } },
                { type: 'text', text: ' ' },
            ]).run()
        }
        return false
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
window.sendComplaint = function (el) {
    confirm(__('confirm_complain_submit'), function (result) {
        if (!result) return

        ajax({
            data: { id: el.dataset.id, type: el.dataset.type, page: el.dataset.page },
            dataType: 'json', type: 'post', url: '/ajax/complaint',
            success: function (data) {
                el.outerHTML = '<i class="fa fa-bell-slash text-muted"></i>'
                data.success ? notyf.success(__('complain_submitted')) : notyf.error(data.message)
            }
        })
    })

    return false
}

/* Добавление или удаление закладок */
window.bookmark = function (el) {
    ajax({
        data: { tid: el.dataset.tid },
        dataType: 'json', type: 'post', url: '/forums/bookmarks/perform',
        success: function (data) {
            if (!data.success) { notyf.error(data.message); return }

            notyf.success(data.message)
            el.textContent = data.type === 'added' ? el.dataset.from : el.dataset.to
        }
    })

    return false
}

/* Удаление записей */
window.deletePost = function (el) {
    confirm(__('confirm_message_delete'), function (result) {
        if (!result) return

        ajax({
            url: el.getAttribute('href'), type: 'delete', dataType: 'json',
            success: function (data) {
                if (data.success) {
                    notyf.success(data.message)
                    el.closest('.section').style.display = 'none'
                } else {
                    notyf.error(data.message)
                }
            }
        })
    })

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
        if (editor) editor.commands.setContent(text, true)
    }

    modalEl.addEventListener('shown.bs.modal', onShown)
    bootstrap.Modal.getOrCreateInstance(modalEl).show()

    return false
}

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
window.deleteComment = function (el) {
    confirm(__('confirm_message_delete'), function (result) {
        if (!result) return

        ajax({
            dataType: 'json', type: 'delete', url: '/comments/' + el.dataset.id,
            success: function (data) {
                if (data.success) {
                    notyf.success(__('message_deleted'))
                    el.closest('.section').style.display = 'none'
                } else {
                    notyf.error(data.message)
                }
            }
        })
    })

    return false
}

/* Изменение рейтинга */
window.changeRating = function (el) {
    ajax({
        data: { id: el.dataset.id, type: el.dataset.type, vote: el.dataset.vote },
        dataType: 'json', type: 'post', url: '/ajax/rating',
        success: function (data) {
            if (data.success) {
                const ratingBlock = el.closest('.js-rating')
                ratingBlock?.querySelectorAll('a').forEach(a => a.classList.remove('active'))
                if (!data.cancel) el.classList.add('active')
                const rating = ratingBlock?.querySelector('b')
                if (rating) rating.innerHTML = data.rating
            } else if (data.message) {
                notyf.error(data.message)
            }
        }
    })

    return false
}

/* Удаляет запись из истории рейтинга */
window.deleteRating = function (el) {
    confirm(__('confirm_message_delete'), function (result) {
        if (!result) return

        ajax({
            data: { id: el.dataset.id },
            dataType: 'json', type: 'post', url: '/ratings/delete',
            success: (data) => {
                if (data.success) {
                    notyf.success(__('record_deleted'))
                    el.closest('.section').style.display = 'none'
                } else {
                    notyf.error(data.message)
                }
            }
        })
    })

    return false
}

/* Удаляет запись из списка жалоб */
window.deleteSpam = function (el) {
    ajax({
        data: { id: el.dataset.id },
        dataType: 'json', type: 'post', url: '/admin/spam/delete',
        success: function (data) {
            if (data.success) {
                notyf.success(__('record_deleted'))
                el.closest('.section').style.display = 'none'
            } else {
                notyf.error(data.message)
            }
        }
    })

    return false
}

/* Удаляет запись со стены сообщений */
window.deleteWall = function (el) {
    confirm(__('confirm_message_delete'), function (result) {
        if (!result) return

        ajax({
            data: { id: el.dataset.id, login: el.dataset.login },
            dataType: 'json', type: 'post', url: '/walls/' + el.dataset.login + '/delete',
            success: function (data) {
                if (data.success) {
                    notyf.success(__('record_deleted'))
                    el.closest('.section').style.display = 'none'
                } else {
                    notyf.error(data.message)
                }
            }
        })
    })

    return false
}

/* Копирует текст в input */
window.copyToClipboard = function (el) {
    const group = el.closest('.input-group')
    const input = group?.querySelector('input')
    if (input) { input.select(); document.execCommand('copy') }

    const tooltipEl = group?.querySelector('.input-group-text')
    if (tooltipEl) {
        tooltipEl.setAttribute('data-bs-original-title', __('copied'))
        const tip = bootstrap.Tooltip.getOrCreateInstance(tooltipEl)
        tip.update()
        tip.show()
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

            const templateEl = scope.querySelector(data.type === 'image' ? '.js-image-template' : '.js-file-template')
            const template = templateEl?.cloneNode(true)

            if (data.type === 'image') {
                const img = template?.querySelector('img')
                img?.setAttribute('src', data.path)
                img?.setAttribute('data-source', data.source)
            } else {
                const link = template?.querySelector('.js-file-link')
                if (link) { link.href = data.path; link.textContent = data.name }
                const sizeEl = template?.querySelector('.js-file-size')
                if (sizeEl) sizeEl.textContent = data.size
            }

            template?.querySelector('.js-file-delete')?.setAttribute('data-id', data.id)
            if (template) filesContainer?.insertAdjacentHTML('beforeend', template.innerHTML)
        },
        error: (_, textStatus) => notyf.error('Ошибка загрузки файла: ' + textStatus)
    })

    el.value = ''
    return false
}

/* Загрузка изображения */
window.submitImage = function (el) {
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

            const templateEl = scope.querySelector('.js-image-template')
            const template = templateEl?.cloneNode(true)
            const img = template?.querySelector('img')
            img?.setAttribute('src', data.path)
            img?.setAttribute('data-source', data.source)
            template?.querySelector('a')?.setAttribute('data-id', data.id)
            if (template) filesContainer?.insertAdjacentHTML('beforeend', template.innerHTML)
        },
        error: (_, textStatus) => notyf.error('Ошибка загрузки файла: ' + textStatus)
    })

    el.value = ''
    return false
}

/* Удаление изображения из формы */
window.cutImage = function (path) {
    if (!path) return

    const editor = window._tiptapActiveEditor
    if (!editor) return

    const normalize = (src) => { try { return new URL(src).pathname } catch { return src } }
    const normalizedPath = normalize(path)

    const { state, dispatch } = editor.view
    const tr = state.tr
    const positions = []

    state.doc.descendants(function (node, pos) {
        if (node.type.name === 'image' && normalize(node.attrs.src) === normalizedPath) {
            positions.push({ pos, size: node.nodeSize })
        }
    })

    positions.reverse().forEach(({ pos, size }) => tr.delete(pos, pos + size))

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
                if (data.path) cutImage(data.path)
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
window.getNewMessages = function () {
    const notifyItem = document.querySelector('.js-messages-block .app-nav__item')
    const badge = notifyItem?.querySelector('.badge')
    const titleSpan = document.querySelector('.app-notification__title span')
    const messagesList = document.querySelector('.js-messages-block .js-messages')

    ajax({
        dataType: 'json', type: 'GET', url: '/messages/new',
        beforeSend: () => messagesList?.insertAdjacentHTML('beforeend', '<li class="js-message-spin text-center"><i class="fas fa-spinner fa-spin fa-2x my-2"></i></li>'),
        complete: () => messagesList?.querySelectorAll('.js-message-spin').forEach(s => s.remove()),
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
window.initSlideThumbImage = function (e, el) {
    e.preventDefault()

    const newImg = el.querySelector('img')
    const imgSource = newImg?.dataset.source
    const slider = el.closest('.media-file')
    const mainLink = slider?.querySelector('.slide-main-link')

    if (mainLink) {
        mainLink.setAttribute('href', imgSource)
        const mainImg = mainLink.querySelector('img')
        if (mainImg) {
            mainImg.setAttribute('src', newImg.getAttribute('src'))
            mainImg.dataset.source = imgSource
        }
    }

    slider?.querySelectorAll('.slide-thumb-image').forEach(img => img.classList.remove('active'))
    newImg?.classList.add('active')
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

// Infinite scroll для ленты
const feedContainer = document.getElementById('feed-container')
const feedSentinel  = document.getElementById('feed-sentinel')

if (feedContainer && feedSentinel) {
    let loading   = false
    let autoCount = 0
    const initialPage = parseInt(new URLSearchParams(location.search).get('page') || 1)

    // Скрыть стандартную пагинацию
    feedContainer.querySelectorAll('.feed-pagination').forEach(el => el.classList.add('d-none'))

    // Спиннер перед sentinel
    const loader = document.createElement('div')
    loader.className = 'feed-loader d-none'
    loader.innerHTML = '<span></span><span></span><span></span><span></span><span></span>'
    feedSentinel.before(loader)

    // URL следующей страницы из data-next последнего pagination
    const getNextUrl = () => {
        const items = feedContainer.querySelectorAll('.feed-pagination')
        return items[items.length - 1]?.dataset.next || ''
    }

    // Кнопка "Загрузить ещё" после AUTO_PAGES автозагрузок
    const showLoadMoreButton = () => {
        loadObserver.disconnect()
        const btn = document.createElement('button')
        btn.type = 'button'
        btn.className = 'btn btn-primary d-block mx-auto my-3'
        btn.textContent = __('buttons.load_more')
        btn.addEventListener('click', () => {
            btn.remove()
            autoCount = 0
            loadObserver.observe(feedSentinel)
        })
        feedSentinel.before(btn)
    }

    // Загрузка следующей страницы
    const loadPage = async () => {
        const nextUrl = getNextUrl()
        if (!nextUrl) { loadObserver.disconnect(); return }
        if (loading)  return

        loading = true
        loader.classList.remove('d-none')

        try {
            const response = await fetch(nextUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            const html     = await response.text()
            const temp     = document.createElement('div')
            temp.innerHTML = html

            temp.querySelectorAll('.feed-pagination, .feed-pagination-top').forEach(el => el.classList.add('d-none'))

            feedContainer.append(...temp.children)

            const page = parseInt(new URL(nextUrl).searchParams.get('page'))

            setTimeout(initShortView, 100)
            history.replaceState(null, '', page > 1 ? `/?page=${page}` : '/')

            autoCount++
            if (page % 5 === 0) showLoadMoreButton()
        } finally {
            loading = false
            loader.classList.add('d-none')
        }
    }

    // Observer для подгрузки при приближении к sentinel
    const loadObserver = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting) loadPage()
    }, { rootMargin: '200px' })

    loadObserver.observe(feedSentinel)
}
