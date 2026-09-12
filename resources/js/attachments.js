// Список прикреплённых файлов под формой: шаблон разметки лежит во вьюхе
// (_upload_file / _upload_media), здесь только подстановка данных

/* file: { path, name, size, id, type } — type: image, video или file */
export function renderFile(scope, container, file) {
    const isMedia = file.type === 'image' || file.type === 'video'
    const template = scope?.querySelector(isMedia ? '.js-image-template' : '.js-file-template')?.cloneNode(true)

    if (! template || ! container) {
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

    container.insertAdjacentHTML('beforeend', template.innerHTML)
}
