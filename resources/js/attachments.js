// Список прикреплённых файлов под формой: шаблон разметки лежит во вьюхе
// (_upload_file / _upload_media), здесь только подстановка данных

/* Сколько из выбранных файлов ещё влезает в лимит. Лимит и текст ошибки несёт
   поле загрузки формы (data-max, data-max-message). Сервер проверяет сам, здесь —
   чтобы не рисовать заглушки и не слать запросы, которые он всё равно отклонит */
export function takeAllowed(scope, files, fail) {
    const input = scope?.querySelector('input[type="file"][data-max]')

    if (! input) {
        return files
    }

    const used = scope.querySelectorAll('.js-files .js-file').length
    const allowed = Math.max(0, Number(input.dataset.max) - used)

    if (files.length > allowed) {
        fail(input.dataset.maxMessage)
    }

    return files.slice(0, allowed)
}

/* Заглушка файла, пока он грузится: рамка миниатюры со спиннером.
   Загруженный файл встаёт на её место, поэтому порядок в списке — порядок выбора */
export function renderPending(container, name) {
    if (! container) {
        return null
    }

    const pending = document.createElement('span')
    pending.className = 'js-file-pending thumbnail-wrap me-1'
    pending.title = name
    pending.innerHTML = '<span class="thumbnail d-inline-flex align-items-center justify-content-center text-muted">'
        + '<i class="fas fa-spinner fa-spin fa-2x"></i></span>'

    container.append(pending)

    return pending
}

/* file: { path, name, size, id, type } — type: image, video или file.
   pending — заглушка из renderPending, которую файл заменит */
export function renderFile(scope, container, file, pending = null) {
    const isMedia = file.type === 'image' || file.type === 'video'
    const template = scope?.querySelector(isMedia ? '.js-image-template' : '.js-file-template')?.cloneNode(true)

    if (! template || ! container) {
        pending?.remove()
        return
    }

    const img = template.querySelector('img')

    if (file.type === 'image') {
        img?.setAttribute('src', file.path)
    } else if (file.type === 'video' && img) {
        // В шаблоне картинка: для видео подменяем её плеером и рисуем значок проигрывания
        const video = Object.assign(document.createElement('video'), {
            src: file.path, className: img.className, preload: 'metadata',
        })
        const wrap = img.parentElement

        img.replaceWith(video)
        wrap?.insertAdjacentHTML('beforeend', '<span class="slide-play-icon">▶</span>')
    } else if (! isMedia) {
        const link = template.querySelector('.js-file-link')
        if (link) {
            link.href = file.path
            link.textContent = file.name
        }

        const size = template.querySelector('.js-file-size')
        if (size) {
            size.textContent = file.size
        }
    }

    template.querySelector('.js-file-delete')?.setAttribute('data-id', file.id)

    if (pending) {
        pending.insertAdjacentHTML('beforebegin', template.innerHTML)
        pending.remove()
    } else {
        container.insertAdjacentHTML('beforeend', template.innerHTML)
    }
}
