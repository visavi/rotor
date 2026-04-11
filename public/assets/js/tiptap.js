import { Editor, Extension, Node, mergeAttributes } from '@tiptap/core'
import { StarterKit } from '@tiptap/starter-kit'
import { TextAlign } from '@tiptap/extension-text-align'
import { TextStyle, FontSize } from '@tiptap/extension-text-style'
import { Color } from '@tiptap/extension-color'
import { Image } from '@tiptap/extension-image'
import { Placeholder } from '@tiptap/extension-placeholder'
import { Mention } from '@tiptap/extension-mention'
import { suggestion } from './tiptap-mention-suggestion.js'

const BackgroundColor = Extension.create({
    name: 'backgroundColor',
    addOptions() { return { types: ['textStyle'] } },
    addGlobalAttributes() {
        return [{
            types: this.options.types,
            attributes: {
                backgroundColor: {
                    default: null,
                    parseHTML: el => el.style.backgroundColor || null,
                    renderHTML: attrs => attrs.backgroundColor
                        ? { style: `background-color: ${attrs.backgroundColor}` }
                        : {},
                },
            },
        }]
    },
    addCommands() {
        return {
            setHighlight: ({ color }) => ({ chain }) =>
                chain().setMark('textStyle', { backgroundColor: color }).run(),
            unsetHighlight: () => ({ chain }) =>
                chain().setMark('textStyle', { backgroundColor: null }).removeEmptyTextStyle().run(),
        }
    },
})
// ─── Blockquote ───────────────────────────────────────────────────────────────

const Blockquote = Node.create({
    name: 'blockquote',
    group: 'block',
    content: 'block+',
    defining: true,
    addAttributes() {
        return { author: { default: null } }
    },
    parseHTML() {
        return [
            {
                tag: 'blockquote',
                getAttrs: el => ({ author: el.querySelector(':scope > footer')?.textContent?.trim() || null }),
                contentElement: el => el.querySelector(':scope > div') || el,
            },
        ]
    },
    renderHTML({ node }) {
        if (node.attrs.author) {
            return ['blockquote', {},
                ['div', {}, 0],
                ['footer', {}, node.attrs.author],
            ]
        }
        return ['blockquote', {}, 0]
    },
    addCommands() {
        return {
            toggleBlockquote: (author = null) => ({ commands }) =>
                commands.toggleWrap(this.name, { author }),
        }
    },
    addKeyboardShortcuts() {
        return { 'Mod-Shift-b': () => this.editor.commands.toggleBlockquote() }
    },
})

// ─── VideoEmbed ───────────────────────────────────────────────────────────────

function getEmbedUrl(url) {
    let m
    m = url.match(/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/)
    if (m) return `https://www.youtube.com/embed/${m[1]}`
    m = url.match(/vimeo\.com\/(\d+)/)
    if (m) return `https://player.vimeo.com/video/${m[1]}`
    m = url.match(/rutube\.ru\/video\/([a-f0-9]+)/)
    if (m) return `https://rutube.ru/play/embed/${m[1]}/`
    m = url.match(/coub\.com\/view\/([a-zA-Z0-9]+)/)
    if (m) return `https://coub.com/embed/${m[1]}`
    m = url.match(/vk\.com\/video(-?\d+_\d+)/)
    if (m) { const [oid, id] = m[1].split('_'); return `https://vk.com/video_ext.php?oid=${oid}&id=${id}&hd=2` }
    m = url.match(/ok\.ru\/video\/(\d+)/)
    if (m) return `https://ok.ru/videoembed/${m[1]}`
    return null
}

function getOriginalUrl(embedUrl) {
    let m
    m = embedUrl.match(/youtube\.com\/embed\/([a-zA-Z0-9_-]{11})/)
    if (m) return `https://www.youtube.com/watch?v=${m[1]}`
    m = embedUrl.match(/player\.vimeo\.com\/video\/(\d+)/)
    if (m) return `https://vimeo.com/${m[1]}`
    m = embedUrl.match(/rutube\.ru\/play\/embed\/([a-f0-9]+)/)
    if (m) return `https://rutube.ru/video/${m[1]}/`
    m = embedUrl.match(/coub\.com\/embed\/([a-zA-Z0-9]+)/)
    if (m) return `https://coub.com/view/${m[1]}`
    m = embedUrl.match(/vk\.com\/video_ext\.php\?oid=(-?\d+)&id=(\d+)/)
    if (m) return `https://vk.com/video${m[1]}_${m[2]}`
    m = embedUrl.match(/ok\.ru\/videoembed\/(\d+)/)
    if (m) return `https://ok.ru/video/${m[1]}`
    return embedUrl
}

const VideoEmbed = Node.create({
    name: 'videoEmbed',
    group: 'block',
    atom: true,
    addAttributes() { return { src: { default: null } } },
    parseHTML() { return [{ tag: 'div.block-video', getAttrs: el => ({ src: getOriginalUrl(el.querySelector('iframe')?.src || '') }) }] },
    renderHTML({ node }) {
        const embedSrc = getEmbedUrl(node.attrs.src)
        if (!embedSrc) return ['div', { class: 'block-video' }]
        return ['div', { class: 'block-video' },
            ['iframe', { src: embedSrc, allowfullscreen: 'true', frameborder: '0', loading: 'lazy' }]]
    },
    addNodeView() {
        return ({ node }) => {
            const dom = document.createElement('div')
            dom.setAttribute('contenteditable', 'false')

            const inner = document.createElement('div')
            inner.className = 'block-video'

            const embedSrc = getEmbedUrl(node.attrs.src)

            let iframe = null
            if (embedSrc) {
                iframe = document.createElement('iframe')
                iframe.src = embedSrc
                iframe.setAttribute('allowfullscreen', 'true')
                iframe.setAttribute('frameborder', '0')
                iframe.loading = 'lazy'
                inner.appendChild(iframe)
            }
            dom.appendChild(inner)

            return {
                dom,
                update(updatedNode) {
                    if (updatedNode.type.name !== 'videoEmbed') return false
                    const newSrc = getEmbedUrl(updatedNode.attrs.src)
                    if (iframe && newSrc) {
                        iframe.src = newSrc
                    }
                    return true
                },
            }
        }
    },
    addCommands() {
        return { insertVideo: src => ({ commands }) => commands.insertContent({ type: this.name, attrs: { src } }) }
    },
})

// ─── AudioEmbed ───────────────────────────────────────────────────────────────

const AudioEmbed = Node.create({
    name: 'audioEmbed',
    group: 'block',
    atom: true,
    addAttributes() { return { src: { default: null } } },
    parseHTML() { return [{ tag: 'audio[controls]', getAttrs: el => ({ src: el.getAttribute('src') }) }] },
    renderHTML({ node }) {
        return ['audio', mergeAttributes({ controls: 'true' }, { src: node.attrs.src })]
    },
    addCommands() {
        return { insertAudio: src => ({ commands }) => commands.insertContent({ type: this.name, attrs: { src } }) }
    },
})

// ─── Sticker ──────────────────────────────────────────────────────────────────

const Sticker = Node.create({
    name: 'sticker',
    group: 'inline',
    inline: true,
    atom: true,
    addAttributes() {
        return {
            src: { default: null },
            alt: { default: null },
        }
    },
    parseHTML() {
        return [{ tag: 'img.sticker', priority: 100 }]
    },
    renderHTML({ node }) {
        return ['img', { class: 'sticker', src: node.attrs.src, alt: node.attrs.alt }]
    },
    addCommands() {
        return {
            insertSticker: attrs => ({ commands }) => commands.insertContent({ type: this.name, attrs }),
        }
    },
})

// ─── Spoiler ──────────────────────────────────────────────────────────────────

const Spoiler = Node.create({
    name: 'spoiler',
    group: 'block',
    content: 'block+',
    defining: true,
    addAttributes() { return { title: { default: 'Spoiler' } } },
    parseHTML() {
        return [{ tag: 'details.block-spoiler', getAttrs: el => ({ title: el.querySelector('summary')?.textContent?.trim() || 'Spoiler' }) }]
    },
    renderHTML({ node }) {
        return ['details', { class: 'block-spoiler', open: true },
            ['summary', {}, node.attrs.title],
            ['div', {}, 0]]
    },
    addCommands() {
        return {
            insertSpoiler: title => ({ commands }) => commands.insertContent({
                type: this.name, attrs: { title }, content: [{ type: 'paragraph' }],
            }),
        }
    },
})

// ─── Hide ─────────────────────────────────────────────────────────────────────

const Hide = Node.create({
    name: 'hide',
    group: 'block',
    content: 'block+',
    defining: true,
    parseHTML() { return [{ tag: 'div.block-hidden' }] },
    renderHTML() { return ['div', { class: 'block-hidden' }, 0] },
    addCommands() {
        return {
            insertHide: () => ({ commands }) => commands.insertContent({
                type: this.name, content: [{ type: 'paragraph' }],
            }),
        }
    },
})

// ─── Utils ────────────────────────────────────────────────────────────────────

function rgbToHex(html) {
    return html.replace(/rgb\((\d+),\s*(\d+),\s*(\d+)\)/g, (_, r, g, b) =>
        '#' + [r, g, b].map(x => parseInt(x).toString(16).padStart(2, '0')).join('')
    )
}

function validateUrl(url) {
    if (!url) return false
    if (!/^https?:\/\//i.test(url)) {
        alert('Введите корректный URL (должен начинаться с http:// или https://)')
        return false
    }
    return true
}

function positionDropdown(btn, menu) {
    const rect = btn.getBoundingClientRect()
    menu.style.top  = (rect.bottom + 4) + 'px'
    menu.style.left = rect.left + 'px'
    requestAnimationFrame(() => {
        const overflow = menu.getBoundingClientRect().right - window.innerWidth + 8
        if (overflow > 0) menu.style.left = Math.max(8, parseFloat(menu.style.left) - overflow) + 'px'
    })
}

// ─── Toolbar ──────────────────────────────────────────────────────────────────

const COLORS = [
    { color: '#6b7280', title: 'Серый'      },
    { color: '#f59e0b', title: 'Жёлтый'    },
    { color: '#f97316', title: 'Оранжевый' },
    { color: '#ef4444', title: 'Красный'   },
    { color: '#3b82f6', title: 'Синий'     },
    { color: '#8b5cf6', title: 'Фиолетовый'},
    { color: '#22c55e', title: 'Зелёный'   },
    { color: '#ec4899', title: 'Розовый'   },
    { color: '#06b6d4', title: 'Голубой'   },
]

const BG_COLORS = [
    { color: '#6b7280', title: 'Серый'      },
    { color: '#ca8a04', title: 'Жёлтый'    },
    { color: '#ea580c', title: 'Оранжевый' },
    { color: '#dc2626', title: 'Красный'   },
    { color: '#2563eb', title: 'Синий'     },
    { color: '#7c3aed', title: 'Фиолетовый'},
    { color: '#16a34a', title: 'Зелёный'   },
    { color: '#db2777', title: 'Розовый'   },
    { color: '#0891b2', title: 'Голубой'   },
]

const SIZES = [
    { label: 'Очень мелкий',  value: '0.7em'  },
    { label: 'Мелкий',        value: '0.85em' },
    { label: 'Нормальный',    value: null      },
    { label: 'Крупный',       value: '1.3em'  },
    { label: 'Очень крупный', value: '1.6em'  },
]

document.addEventListener('click', () => {
    document.querySelectorAll('.tiptap-dropdown-menu.is-open')
        .forEach(m => m.classList.remove('is-open'))
})

document.addEventListener('scroll', () => {
    document.querySelectorAll('.tiptap-dropdown-menu.is-open').forEach(m => {
        if (m._anchorBtn) positionDropdown(m._anchorBtn, m)
    })
}, true)

// ─── Sticker picker ───────────────────────────────────────────────────────────

let stickerCache = null

async function getStickerData() {
    if (stickerCache) return stickerCache
    try {
        const resp = await fetch('/ajax/getstickers')
        stickerCache = await resp.json() // [{name: '/uploads/...', code: ':D'}, ...]
    } catch {
        stickerCache = []
    }
    return stickerCache
}

function makeStickerPicker(editor) {
    const wrap = document.createElement('div')
    wrap.className = 'tiptap-dropdown'

    const btn = document.createElement('button')
    btn.type = 'button'
    btn.className = 'tiptap-btn'
    btn.title = 'Стикер'
    btn.innerHTML = '<i class="fas fa-smile"></i>'

    const panel = document.createElement('div')
    panel.className = 'tiptap-dropdown-menu tiptap-sticker-panel'
    document.body.appendChild(panel)

    let loaded = false

    function renderStickers(stickers, grid) {
        grid.innerHTML = ''
        stickers.forEach(({ name, code }) => {
            const img = document.createElement('img')
            img.src = name
            img.alt = code
            img.title = code
            img.addEventListener('mousedown', e => {
                e.preventDefault()
                e.stopPropagation()
                editor.chain().focus().insertSticker({ src: name, alt: code }).run()
                editor.commands.insertContent(' ')
                panel.classList.remove('is-open')
            })
            grid.appendChild(img)
        })
    }

    async function openPanel() {
        if (!loaded) {
            panel.innerHTML = '<span class="tiptap-sticker-loading">...</span>'
            panel.classList.add('is-open')
            positionPanel()

            const categories = await getStickerData()
            panel.innerHTML = ''

            if (!categories.length) return

            const tabs = document.createElement('div')
            tabs.className = 'tiptap-sticker-tabs'

            const grid = document.createElement('div')
            grid.className = 'tiptap-sticker-grid'

            categories.forEach((cat, i) => {
                const tab = document.createElement('button')
                tab.type = 'button'
                tab.className = 'tiptap-sticker-tab' + (i === 0 ? ' is-active' : '')
                tab.textContent = cat.name
                tab.addEventListener('mousedown', e => e.preventDefault())
                tab.addEventListener('click', e => {
                    e.stopPropagation()
                    tabs.querySelectorAll('.tiptap-sticker-tab').forEach(t => t.classList.remove('is-active'))
                    tab.classList.add('is-active')
                    renderStickers(cat.stickers, grid)
                })
                tabs.appendChild(tab)
            })

            panel.appendChild(tabs)
            panel.appendChild(grid)
            renderStickers(categories[0].stickers, grid)
            loaded = true
        } else {
            positionPanel()
            panel.classList.add('is-open')
        }
    }

    function positionPanel() {
        const rect = btn.getBoundingClientRect()
        const spaceBelow = window.innerHeight - rect.bottom
        if (spaceBelow < 300 && rect.top > 300) {
            panel.style.top    = 'auto'
            panel.style.bottom = (window.innerHeight - rect.top + 4) + 'px'
        } else {
            panel.style.top    = (rect.bottom + 4) + 'px'
            panel.style.bottom = 'auto'
        }
        positionDropdown(btn, panel)
    }

    btn.addEventListener('mousedown', e => e.preventDefault())
    btn.addEventListener('click', e => {
        e.stopPropagation()
        const wasOpen = panel.classList.contains('is-open')
        document.querySelectorAll('.tiptap-dropdown-menu.is-open')
            .forEach(m => m.classList.remove('is-open'))
        if (!wasOpen) { panel._anchorBtn = btn; openPanel() }
    })

    wrap.appendChild(btn)
    return wrap
}

function makeDropdown(icon, title, items, extraClass = '') {
    const wrap = document.createElement('div')
    wrap.className = 'tiptap-dropdown'

    const btn = document.createElement('button')
    btn.type = 'button'
    btn.className = 'tiptap-btn'
    btn.title = title
    btn.innerHTML = `<i class="fas ${icon}"></i><i class="fas fa-chevron-down tiptap-dd-arrow"></i>`

    const menu = document.createElement('div')
    menu.className = 'tiptap-dropdown-menu' + (extraClass ? ' ' + extraClass : '')
    items.forEach(item => menu.appendChild(item))
    document.body.appendChild(menu)

    function positionMenu() { positionDropdown(btn, menu) }

    btn.addEventListener('mousedown', e => e.preventDefault())
    btn.addEventListener('click', e => {
        e.stopPropagation()
        const wasOpen = menu.classList.contains('is-open')
        document.querySelectorAll('.tiptap-dropdown-menu.is-open').forEach(m => m.classList.remove('is-open'))
        if (!wasOpen) { positionMenu(); menu._anchorBtn = btn; menu.classList.add('is-open') }
    })

    wrap.appendChild(btn)
    wrap._dropdownBtn = btn
    return wrap
}

function buildToolbar(editor) {
    const bar = document.createElement('div')
    bar.className = 'tiptap-toolbar'

    const activeButtons = []

    function btn(icon, title, action, getActive = null) {
        const el = document.createElement('button')
        el.type = 'button'
        el.className = 'tiptap-btn'
        el.title = title
        el.innerHTML = `<i class="fas ${icon}"></i>`
        el.addEventListener('mousedown', e => { e.preventDefault(); action() })
        if (getActive) activeButtons.push({ el, getActive })
        bar.appendChild(el)
    }

    function sep() {
        const el = document.createElement('span')
        el.className = 'tiptap-sep'
        bar.appendChild(el)
    }

    function dropdown(el) { bar.appendChild(el) }

    btn('fa-bold',          'Жирный (Ctrl+B)',  () => editor.chain().focus().toggleBold().run(),      () => editor.isActive('bold'))
    btn('fa-italic',        'Курсив (Ctrl+I)',   () => editor.chain().focus().toggleItalic().run(),    () => editor.isActive('italic'))
    btn('fa-underline',     'Подчёркнутый',      () => editor.chain().focus().toggleUnderline().run(), () => editor.isActive('underline'))
    btn('fa-strikethrough', 'Зачёркнутый',       () => editor.chain().focus().toggleStrike().run(),   () => editor.isActive('strike'))
    sep()

    const resetSwatch = document.createElement('button')
    resetSwatch.type = 'button'
    resetSwatch.className = 'tiptap-color-swatch tiptap-color-reset'
    resetSwatch.title = 'Сбросить цвет'
    resetSwatch.addEventListener('mousedown', e => { e.preventDefault(); editor.chain().focus().unsetColor().run() })

    const customColorInput = document.createElement('input')
    customColorInput.type = 'color'
    customColorInput.title = 'Свой цвет'
    customColorInput.className = 'tiptap-color-custom'
    customColorInput.addEventListener('input', () => {
        editor.chain().focus().setColor(customColorInput.value).run()
    })

    const colorSwatches = [...COLORS.map(({ color, title }) => {
        const el = document.createElement('button')
        el.type = 'button'
        el.className = 'tiptap-color-swatch'
        el.title = title
        el.style.background = color
        el.addEventListener('mousedown', e => { e.preventDefault(); editor.chain().focus().setColor(color).run() })
        return el
    }), customColorInput, resetSwatch]
    const colorDd = makeDropdown('fa-palette', 'Цвет текста', colorSwatches, 'tiptap-colors-menu')
    dropdown(colorDd)
    activeButtons.push({ el: colorDd._dropdownBtn, getActive: () => !!editor.getAttributes('textStyle').color })

    const resetBgSwatch = document.createElement('button')
    resetBgSwatch.type = 'button'
    resetBgSwatch.className = 'tiptap-color-swatch tiptap-color-reset'
    resetBgSwatch.title = 'Сбросить фон'
    resetBgSwatch.addEventListener('mousedown', e => { e.preventDefault(); editor.chain().focus().unsetHighlight().run() })

    const customBgInput = document.createElement('input')
    customBgInput.type = 'color'
    customBgInput.title = 'Свой цвет фона'
    customBgInput.className = 'tiptap-color-custom'
    customBgInput.addEventListener('input', () => {
        editor.chain().focus().setHighlight({ color: customBgInput.value }).run()
    })

    const bgSwatches = [...BG_COLORS.map(({ color, title }) => {
        const el = document.createElement('button')
        el.type = 'button'
        el.className = 'tiptap-color-swatch'
        el.title = title
        el.style.background = color
        el.addEventListener('mousedown', e => { e.preventDefault(); editor.chain().focus().setHighlight({ color }).run() })
        return el
    }), customBgInput, resetBgSwatch]
    const bgDd = makeDropdown('fa-fill-drip', 'Цвет фона', bgSwatches, 'tiptap-colors-menu')
    dropdown(bgDd)
    activeButtons.push({ el: bgDd._dropdownBtn, getActive: () => !!editor.getAttributes('textStyle').backgroundColor })

    const sizeItems = SIZES.map(({ label, value }) => {
        const el = document.createElement('button')
        el.type = 'button'
        el.className = 'tiptap-size-item'
        el.textContent = label
        el.addEventListener('mousedown', e => {
            e.preventDefault()
            value ? editor.chain().focus().setFontSize(value).run()
                  : editor.chain().focus().unsetFontSize().run()
        })
        return el
    })
    const sizeDd = makeDropdown('fa-font', 'Размер шрифта', sizeItems)
    dropdown(sizeDd)
    activeButtons.push({ el: sizeDd._dropdownBtn, getActive: () => !!editor.getAttributes('textStyle').fontSize })
    sep()

    const alignItems = [
        { icon: 'fa-align-left',   title: 'По левому краю',  align: 'left'   },
        { icon: 'fa-align-center', title: 'По центру',        align: 'center' },
        { icon: 'fa-align-right',  title: 'По правому краю', align: 'right'  },
    ].map(({ icon, title, align }) => {
        const el = document.createElement('button')
        el.type = 'button'
        el.className = 'tiptap-menu-item'
        el.innerHTML = `<i class="fas ${icon}"></i> ${title}`
        el.addEventListener('mousedown', e => { e.preventDefault(); editor.chain().focus().setTextAlign(align).run() })
        return el
    })
    const alignDd = makeDropdown('fa-align-left', 'Выравнивание', alignItems)
    dropdown(alignDd)
    activeButtons.push({ el: alignDd._dropdownBtn, getActive: () =>
        editor.isActive({ textAlign: 'center' }) || editor.isActive({ textAlign: 'right' })
    })

    const listItems = [
        { icon: 'fa-list-ul', title: 'Маркированный список', action: () => editor.chain().focus().toggleBulletList().run()  },
        { icon: 'fa-list-ol', title: 'Нумерованный список',  action: () => editor.chain().focus().toggleOrderedList().run() },
    ].map(({ icon, title, action }) => {
        const el = document.createElement('button')
        el.type = 'button'
        el.className = 'tiptap-menu-item'
        el.innerHTML = `<i class="fas ${icon}"></i> ${title}`
        el.addEventListener('mousedown', e => { e.preventDefault(); action() })
        return el
    })
    const listDd = makeDropdown('fa-list-ul', 'Списки', listItems)
    dropdown(listDd)
    activeButtons.push({ el: listDd._dropdownBtn, getActive: () =>
        editor.isActive('bulletList') || editor.isActive('orderedList')
    })
    sep()

    btn('fa-link', 'Ссылка', () => {
        const existing = editor.getAttributes('link').href || ''
        const { from, to } = editor.state.selection
        const selected = editor.state.doc.textBetween(from, to, '')
        const url = prompt('URL ссылки:', existing || selected)
        if (!validateUrl(url)) return
        if (selected || existing) {
            editor.chain().focus().extendMarkRange('link').setLink({ href: url, target: '_blank' }).run()
        } else {
            const placeholder = 'Текст ссылки...'
            editor.chain().focus()
                .insertContent(`<a href="${url}" target="_blank">${placeholder}</a>`)
                .run()
            // Выделяем вставленный текст
            const { from } = editor.state.selection
            editor.commands.setTextSelection({ from: from - placeholder.length, to: from })
        }
    }, () => editor.isActive('link'))

    btn('fa-image', 'Изображение', async () => {
        const url = prompt('URL изображения:')
        if (!validateUrl(url)) return

        const imagePattern = /\.(jpe?g|png|gif|webp|bmp|svg)(\?.*)?$/i
        if (imagePattern.test(url)) {
            editor.chain().focus().setImage({ src: url }).run()
            return
        }

        const data = await fetch('/ajax/resolve-image?url=' + encodeURIComponent(url)).then(r => r.json())
        if (data.image) {
            editor.chain().focus().setImage({ src: data.image }).run()
        } else {
            editor.chain().focus().setImage({ src: url }).run()
            toastr.warning('Прямая ссылка на картинку не найдена')
        }
    })

    btn('fa-play-circle', 'Видео (YouTube, VK, Rutube, Vimeo, Coub, Ok)', () => {
        const url = prompt('URL видео:')
        if (!validateUrl(url)) return
        editor.chain().focus().insertVideo(url).run()
    })

    btn('fa-music', 'Аудио (mp3, ogg, wav)', () => {
        const url = prompt('URL аудио:')
        if (!validateUrl(url)) return
        editor.chain().focus().insertAudio(url).run()
    })
    sep()

    btn('fa-plus-square', 'Спойлер', () => {
        if (editor.isActive('spoiler')) {
            editor.chain().focus().lift('spoiler').run()
        } else {
            const title = prompt('Название спойлера:', 'Спойлер')
            if (title !== null) editor.chain().focus().insertSpoiler(title || 'Спойлер').run()
        }
    }, () => editor.isActive('spoiler'))
    btn('fa-eye-slash', 'Скрытый текст (только для авторизованных)',
        () => editor.isActive('hide')
            ? editor.chain().focus().lift('hide').run()
            : editor.chain().focus().insertHide().run(),
        () => editor.isActive('hide'))
    btn('fa-quote-right', 'Цитата', () => {
        if (editor.isActive('blockquote')) {
            editor.chain().focus().toggleBlockquote().run()
        } else {
            const author = prompt('Автор цитаты (необязательно):')
            if (author !== null) editor.chain().focus().toggleBlockquote(author || null).run()
        }
    }, () => editor.isActive('blockquote'))
    btn('fa-code', 'Блок кода',
        () => editor.chain().focus().toggleCodeBlock().run(),
        () => editor.isActive('codeBlock'))
    sep()

    dropdown(makeStickerPicker(editor))
    sep()

    btn('fa-eraser', 'Очистить форматирование',
        () => editor.chain().focus().unsetAllMarks().clearNodes().run())

    function updateActive() {
        activeButtons.forEach(({ el, getActive }) => el.classList.toggle('is-active', getActive()))
    }
    editor.on('selectionUpdate', updateActive)
    editor.on('transaction', updateActive)

    return bar
}

// ─── Init ─────────────────────────────────────────────────────────────────────

function initEditor(textarea) {
    const maxLength   = parseInt(textarea.getAttribute('maxlength')) || null
    const placeholder = textarea.getAttribute('placeholder') || ''
    const wasRequired = textarea.hasAttribute('required')

    const wrapper = document.createElement('div')
    wrapper.className = 'tiptap-wrapper'
    textarea.parentNode.insertBefore(wrapper, textarea)

    const editorEl = document.createElement('div')
    editorEl.className = 'tiptap-editor-content'
    wrapper.appendChild(editorEl)

    textarea.style.display = 'none'
    textarea.removeAttribute('required')
    textarea.removeAttribute('maxlength') // maxlength по HTML бессмысленен — считаем текст

    let isChanged = false

    const editor = new Editor({
        element: editorEl,
        editorProps: {
            transformPastedText(text) {
                return text
                    .split('\n')
                    .map(line => line.trimEnd())
                    .join('\n')
                    .replace(/\n{3,}/g, '\n\n')
            },
            transformPastedHTML(html) {
                const doc = new DOMParser().parseFromString(html, 'text/html')
                doc.querySelectorAll('*').forEach(el => {
                    el.removeAttribute('class')
                    el.removeAttribute('style')
                })
                // Убираем последовательные пустые параграфы
                let prevEmpty = false
                doc.body.querySelectorAll('p').forEach(p => {
                    const isEmpty = !p.textContent.trim() && !p.querySelector('img,audio,video,iframe')
                    if (isEmpty && prevEmpty) p.remove()
                    prevEmpty = isEmpty
                })
                return doc.body.innerHTML
            },
        },
        extensions: [
            StarterKit.configure({
                blockquote: false,
                codeBlock: { HTMLAttributes: { class: 'block-code' } },
            }),
            Blockquote,
            TextStyle,
            Color,
            BackgroundColor,
            FontSize,
            TextAlign.configure({ types: ['heading', 'paragraph'] }),
            Image.configure({ inline: false, HTMLAttributes: { class: 'block-image' } }),
            Placeholder.configure({ placeholder }),
            VideoEmbed,
            AudioEmbed,
            Spoiler,
            Hide,
            Sticker,
            Mention.configure({
                HTMLAttributes: { class: 'mention' },
                renderHTML({ options, node }) {
                    return ['a', mergeAttributes(options.HTMLAttributes, { href: '/users/' + node.attrs.id }), '@' + node.attrs.id]
                },
                suggestion,
            }),
        ],
        content: textarea.value || '',
        onUpdate({ editor }) {
            textarea.value = rgbToHex(editor.getHTML())
            isChanged = true
            updateCounter()
        },
        onCreate({ editor }) {
            textarea.value = rgbToHex(editor.getHTML())
            // Если документ начинается или заканчивается атомарным узлом —
            // добавляем пустые параграфы, иначе некуда поставить курсор
            const { state } = editor
            const { schema } = state
            let tr = state.tr
            let modified = false
            if (tr.doc.firstChild?.isAtom) {
                tr = tr.insert(0, schema.nodes.paragraph.create())
                modified = true
            }
            if (tr.doc.lastChild?.isAtom) {
                tr = tr.insert(tr.doc.content.size, schema.nodes.paragraph.create())
                modified = true
            }
            if (modified) {
                editor.view.dispatch(tr)
                isChanged = false
            }
        },
    })

    window.addEventListener('beforeunload', e => {
        if (isChanged && !editor.isEmpty) { e.preventDefault(); return e.returnValue = '' }
    })
    textarea.closest('form')?.addEventListener('submit', () => {
        isChanged = false
        textarea.value = textarea.value
            .replace(/(<p><\/p>)+/g, '<p></p>')  // схлопываем несколько пустых p в один
            .replace(/^(<p><\/p>)+/, '')           // убираем пустые p в начале
            .replace(/(<p><\/p>)+$/, '')           // убираем пустые p в конце
    })

    // Клик по пустой области контейнера (ниже контента) ставит курсор в конец
    editorEl.addEventListener('click', e => {
        if (e.target === editorEl) editor.commands.focus('end')
    })


    window._tiptapActiveEditor = editor
    editor.on('focus', () => { window._tiptapActiveEditor = editor })

    const toolbar = buildToolbar(editor)
    wrapper.insertBefore(toolbar, editorEl)

    const counterEl = textarea.parentNode.querySelector('.js-textarea-counter')
    function updateCounter() {
        if (!counterEl) return
        // Считаем как PHP strip_tags — без разделителей между блоками
        const len = editor.isEmpty ? 0 : editor.state.doc.textContent.length
        counterEl.textContent = maxLength ? `${len} / ${maxLength}` : (len || '')
        if (maxLength) counterEl.classList.toggle('text-danger', len > maxLength)
    }
    updateCounter()

    if (wasRequired) {
        const form = textarea.closest('form')
        if (form) {
            form.addEventListener('submit', e => {
                if (editor.isEmpty) {
                    e.preventDefault()
                    editorEl.classList.add('is-invalid')
                    editorEl.scrollIntoView({ behavior: 'smooth', block: 'center' })
                } else {
                    editorEl.classList.remove('is-invalid')
                }
            }, { capture: true })
        }
    }

    return editor
}

// ─── Export ───────────────────────────────────────────────────────────────────

export function initEditors(textareas) {
    textareas.forEach(initEditor)
}
