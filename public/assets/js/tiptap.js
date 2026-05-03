import { Editor, Extension, Node, mergeAttributes } from '@tiptap/core'
import { StarterKit } from '@tiptap/starter-kit'
import { TextAlign } from '@tiptap/extension-text-align'
import { TextStyle, FontSize } from '@tiptap/extension-text-style'
import { Color } from '@tiptap/extension-color'
import { Image } from '@tiptap/extension-image'
import { Placeholder } from '@tiptap/extension-placeholder'
import { CharacterCount } from '@tiptap/extension-character-count'
import { Mention } from '@tiptap/extension-mention'
import FileHandler from '@tiptap/extension-file-handler'
import { Table, TableRow, TableHeader, TableCell } from '@tiptap/extension-table'
import { trans as __ } from './translate.js'

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
    parseHTML() { return [{ tag: 'div.video', getAttrs: el => ({ src: getOriginalUrl(el.querySelector('iframe')?.src || '') }) }] },
    renderHTML({ node }) {
        const embedSrc = getEmbedUrl(node.attrs.src)
        if (!embedSrc) return ['div', { class: 'video' }]
        return ['div', { class: 'video' },
            ['iframe', { src: embedSrc, allowfullscreen: 'true', frameborder: '0', loading: 'lazy' }]]
    },
    addNodeView() {
        return ({ node }) => {
            const dom = document.createElement('div')
            dom.setAttribute('contenteditable', 'false')

            const inner = document.createElement('div')
            inner.className = 'video'

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
    addAttributes() {
        return {
            title: { default: 'Spoiler' },
            open:  { default: true },
        }
    },
    parseHTML() {
        return [{
            tag: 'details.spoiler',
            getAttrs: el => ({
                title: el.querySelector(':scope > summary')?.textContent?.trim() || 'Spoiler',
                open: true,
            }),
            contentElement: el => el.querySelector(':scope > div') || el,
        }]
    },
    renderHTML({ node }) {
        // open не сохраняем в HTML — для читателей спойлер закрыт по умолчанию
        return ['details', { class: 'spoiler' },
            ['summary', {}, node.attrs.title],
            ['div', {}, 0]]
    },
    addNodeView() {
        return ({ node: initNode, getPos, editor }) => {
            let attrs = { ...initNode.attrs }

            const dom = document.createElement('details')
            dom.className = 'spoiler'
            dom.open = attrs.open

            const summary = document.createElement('summary')
            summary.setAttribute('contenteditable', 'false')
            summary.textContent = attrs.title || 'Spoiler'

            summary.addEventListener('mousedown', e => {
                e.preventDefault()
                e.stopPropagation()
                const pos = typeof getPos === 'function' ? getPos() : null
                if (pos !== null) {
                    editor.view.dispatch(
                        editor.view.state.tr.setNodeMarkup(pos, null, { ...attrs, open: !attrs.open })
                    )
                }
            })

            const contentDOM = document.createElement('div')
            dom.appendChild(summary)
            dom.appendChild(contentDOM)

            return {
                dom,
                contentDOM,
                update(updatedNode) {
                    if (updatedNode.type !== initNode.type) return false
                    attrs = { ...updatedNode.attrs }
                    summary.textContent = attrs.title || 'Spoiler'
                    dom.open = attrs.open
                    return true
                },
            }
        }
    },
    addCommands() {
        return {
            insertSpoiler: title => ({ commands }) => commands.insertContent({
                type: this.name, attrs: { title, open: true }, content: [{ type: 'paragraph' }],
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
    parseHTML() { return [{ tag: 'div.hidden' }] },
    renderHTML() { return ['div', { class: 'hidden' }, 0] },
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

// Когда clearNodes() конвертирует code block в параграф, переносы строк
// становятся буквальными \n внутри <p>, которые браузер схлопывает в пробел.
// Нормализуем их в <br>.
function fixNewlines(html) {
    return html.replace(/(<p\b[^>]*>)([\s\S]*?)(<\/p>)/g, (_, open, content, close) =>
        open + content.replace(/\n/g, '<br>') + close
    )
}

function validateUrl(url) {
    if (!url) return false
    if (!/^https?:\/\//i.test(url)) {
        alert(__('editor.invalid_url'))
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
    { color: '#6b7280' },
    { color: '#f59e0b' },
    { color: '#f97316' },
    { color: '#ef4444' },
    { color: '#3b82f6' },
    { color: '#8b5cf6' },
    { color: '#22c55e' },
    { color: '#ec4899' },
    { color: '#06b6d4' },
]

const BG_COLORS = [
    { color: '#6b7280' },
    { color: '#ca8a04' },
    { color: '#ea580c' },
    { color: '#dc2626' },
    { color: '#2563eb' },
    { color: '#7c3aed' },
    { color: '#16a34a' },
    { color: '#db2777' },
    { color: '#0891b2' },
]

const SIZES = [
    { get label() { return __('editor.size_xs') }, value: '0.7em'  },
    { get label() { return __('editor.size_sm') }, value: '0.85em' },
    { get label() { return __('editor.size_md') }, value: null      },
    { get label() { return __('editor.size_lg') }, value: '1.3em'  },
    { get label() { return __('editor.size_xl') }, value: '1.6em'  },
]

document.addEventListener('click', () => {
    document.querySelectorAll('.tiptap-dropdown-menu.is-open')
        .forEach(m => m.classList.remove('is-open'))
})

document.addEventListener('scroll', (e) => {
    document.querySelectorAll('.tiptap-dropdown-menu.is-open').forEach(m => {
        if (m.contains(e.target)) return  // скролл внутри самого меню — не трогаем позицию
        if (m._reposition) m._reposition()
        else if (m._anchorBtn) positionDropdown(m._anchorBtn, m)
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
    btn.title = __('editor.sticker')
    btn.innerHTML = '<i class="fas fa-smile"></i><i class="fas fa-chevron-down tiptap-dd-arrow"></i>'

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
            positionPanel()
        } else {
            positionPanel()
            panel.classList.add('is-open')
        }
    }

    function positionPanel() {
        const vw   = window.innerWidth
        const rect = btn.getBoundingClientRect()
        // Горизонталь: ограничиваем ширину экраном и прижимаем влево если вылезает
        const panelWidth = Math.min(360, vw - 16)
        panel.style.width = panelWidth + 'px'
        panel.style.left  = Math.max(8, Math.min(rect.left, vw - panelWidth - 8)) + 'px'
        // Вертикаль: вниз или вверх в зависимости от места
        const spaceBelow = window.innerHeight - rect.bottom
        if (spaceBelow < 300 && rect.top > 300) {
            panel.style.top    = 'auto'
            panel.style.bottom = (window.innerHeight - rect.top + 4) + 'px'
        } else {
            panel.style.top    = (rect.bottom + 4) + 'px'
            panel.style.bottom = 'auto'
        }
    }

    btn.addEventListener('mousedown', e => e.preventDefault())
    btn.addEventListener('click', e => {
        e.stopPropagation()
        const wasOpen = panel.classList.contains('is-open')
        document.querySelectorAll('.tiptap-dropdown-menu.is-open')
            .forEach(m => m.classList.remove('is-open'))
        if (!wasOpen) { panel._anchorBtn = btn; panel._reposition = positionPanel; openPanel() }
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

// ─── Mention suggestion ───────────────────────────────────────────────────────

const suggestion = {
    char: '@',
    minLength: 2,

    items: async ({ query }) => {
        if (query.length < 2) return []
        const res = await fetch('/search-users?query=' + encodeURIComponent(query))
        return res.ok ? await res.json() : []
    },

    render: () => {
        let el, selectedIndex = 0, items = [], command

        function render() {
            el.innerHTML = ''
            el.style.display = items.length ? 'block' : 'none'
            items.forEach((item, i) => {
                const div = document.createElement('div')
                div.className = 'mention-item' + (i === selectedIndex ? ' mention-item-selected' : '')
                div.textContent = '@' + item.login + (item.name && item.name !== item.login ? ' — ' + item.name : '')
                div.addEventListener('mousedown', e => {
                    e.preventDefault()
                    command(item)
                })
                el.appendChild(div)
            })
        }

        return {
            onStart(props) {
                command = props.command
                items = props.items
                selectedIndex = 0

                el = document.createElement('div')
                el.className = 'mention-dropdown'
                el.style.display = 'none'
                document.body.appendChild(el)

                const rect = props.clientRect()
                el.style.position = 'fixed'
                el.style.top = rect.bottom + 'px'
                el.style.left = rect.left + 'px'

                render()
            },

            onUpdate(props) {
                command = props.command
                items = props.items
                selectedIndex = 0

                const rect = props.clientRect()
                el.style.top = rect.bottom + 'px'
                el.style.left = rect.left + 'px'

                render()
            },

            onKeyDown(props) {
                if (props.event.key === 'ArrowDown') {
                    selectedIndex = (selectedIndex + 1) % items.length
                    render()
                    return true
                }
                if (props.event.key === 'ArrowUp') {
                    selectedIndex = (selectedIndex - 1 + items.length) % items.length
                    render()
                    return true
                }
                if (props.event.key === 'Enter') {
                    if (items[selectedIndex]) command(items[selectedIndex])
                    return true
                }
                return false
            },

            onExit() {
                el?.remove()
            },
        }
    },

    command({ editor, range, props }) {
        editor.chain().focus().deleteRange(range).insertContent({
            type: 'mention',
            attrs: { id: props.login, label: props.login },
        }).insertContent(' ').run()
    },
}

function buildToolbar(editor, textarea, uploadImageFn) {
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

    btn('fa-bold',          __('editor.bold'),      () => editor.chain().focus().toggleBold().run(),      () => editor.isActive('bold'))
    btn('fa-italic',        __('editor.italic'),    () => editor.chain().focus().toggleItalic().run(),    () => editor.isActive('italic'))
    btn('fa-underline',     __('editor.underline'), () => editor.chain().focus().toggleUnderline().run(), () => editor.isActive('underline'))
    btn('fa-strikethrough', __('editor.strike'),    () => editor.chain().focus().toggleStrike().run(),   () => editor.isActive('strike'))
    sep()

    const resetSwatch = document.createElement('button')
    resetSwatch.type = 'button'
    resetSwatch.className = 'tiptap-color-swatch tiptap-color-reset'
    resetSwatch.title = __('editor.reset_color')
    resetSwatch.addEventListener('mousedown', e => { e.preventDefault(); editor.chain().focus().unsetColor().run() })

    const customColorInput = document.createElement('input')
    customColorInput.type = 'color'
    customColorInput.title = __('editor.custom_color')
    customColorInput.className = 'tiptap-color-custom'
    customColorInput.addEventListener('input', () => {
        editor.chain().focus().setColor(customColorInput.value).run()
    })

    const colorSwatches = [...COLORS.map(({ color }) => {
        const el = document.createElement('button')
        el.type = 'button'
        el.className = 'tiptap-color-swatch'
        el.title = color
        el.style.background = color
        el.addEventListener('mousedown', e => { e.preventDefault(); editor.chain().focus().setColor(color).run() })
        return el
    }), customColorInput, resetSwatch]
    const colorDd = makeDropdown('fa-palette', __('editor.color'), colorSwatches, 'tiptap-colors-menu')
    dropdown(colorDd)
    activeButtons.push({ el: colorDd._dropdownBtn, getActive: () => !!editor.getAttributes('textStyle').color })

    const resetBgSwatch = document.createElement('button')
    resetBgSwatch.type = 'button'
    resetBgSwatch.className = 'tiptap-color-swatch tiptap-color-reset'
    resetBgSwatch.title = __('editor.reset_bg')
    resetBgSwatch.addEventListener('mousedown', e => { e.preventDefault(); editor.chain().focus().unsetHighlight().run() })

    const customBgInput = document.createElement('input')
    customBgInput.type = 'color'
    customBgInput.title = __('editor.custom_bg')
    customBgInput.className = 'tiptap-color-custom'
    customBgInput.addEventListener('input', () => {
        editor.chain().focus().setHighlight({ color: customBgInput.value }).run()
    })

    const bgSwatches = [...BG_COLORS.map(({ color }) => {
        const el = document.createElement('button')
        el.type = 'button'
        el.className = 'tiptap-color-swatch'
        el.title = color
        el.style.background = color
        el.addEventListener('mousedown', e => { e.preventDefault(); editor.chain().focus().setHighlight({ color }).run() })
        return el
    }), customBgInput, resetBgSwatch]
    const bgDd = makeDropdown('fa-fill-drip', __('editor.bg_color'), bgSwatches, 'tiptap-colors-menu')
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
    const sizeDd = makeDropdown('fa-font', __('editor.font_size'), sizeItems)
    dropdown(sizeDd)
    activeButtons.push({ el: sizeDd._dropdownBtn, getActive: () => !!editor.getAttributes('textStyle').fontSize })
    sep()

    const alignItems = [
        { icon: 'fa-align-left',   title: __('editor.align_left'),   align: 'left'   },
        { icon: 'fa-align-center', title: __('editor.align_center'), align: 'center' },
        { icon: 'fa-align-right',  title: __('editor.align_right'),  align: 'right'  },
    ].map(({ icon, title, align }) => {
        const el = document.createElement('button')
        el.type = 'button'
        el.className = 'tiptap-menu-item'
        el.innerHTML = `<i class="fas ${icon}"></i> ${title}`
        el.addEventListener('mousedown', e => { e.preventDefault(); editor.chain().focus().setTextAlign(align).run() })
        return el
    })
    const alignDd = makeDropdown('fa-align-left', __('editor.alignment'), alignItems)
    dropdown(alignDd)
    activeButtons.push({ el: alignDd._dropdownBtn, getActive: () =>
        editor.isActive({ textAlign: 'center' }) || editor.isActive({ textAlign: 'right' })
    })

    const listItems = [
        { icon: 'fa-list-ul', title: __('editor.bullet_list'),  action: () => editor.chain().focus().toggleBulletList().run()  },
        { icon: 'fa-list-ol', title: __('editor.ordered_list'), action: () => editor.chain().focus().toggleOrderedList().run() },
    ].map(({ icon, title, action }) => {
        const el = document.createElement('button')
        el.type = 'button'
        el.className = 'tiptap-menu-item'
        el.innerHTML = `<i class="fas ${icon}"></i> ${title}`
        el.addEventListener('mousedown', e => { e.preventDefault(); action() })
        return el
    })
    const listDd = makeDropdown('fa-list-ul', __('editor.lists'), listItems)
    dropdown(listDd)
    activeButtons.push({ el: listDd._dropdownBtn, getActive: () =>
        editor.isActive('bulletList') || editor.isActive('orderedList')
    })

    function makeMenuSep() {
        const el = document.createElement('hr')
        el.className = 'tiptap-menu-sep'
        return el
    }

    function tableMenuItem(icon, title, action) {
        const el = document.createElement('button')
        el.type = 'button'
        el.className = 'tiptap-menu-item'
        el.innerHTML = `<i class="fas ${icon}"></i> ${title}`
        el.addEventListener('mousedown', e => { e.preventDefault(); action() })
        return el
    }

    const tableMenuItems = [
        tableMenuItem('fa-table',          __('editor.table_insert'),         () => editor.chain().focus().insertTable({ rows: 3, cols: 3, withHeaderRow: true }).run()),
        makeMenuSep(),
        tableMenuItem('fa-arrow-up',       __('editor.table_row_before'),     () => editor.chain().focus().addRowBefore().run()),
        tableMenuItem('fa-arrow-down',     __('editor.table_row_after'),      () => editor.chain().focus().addRowAfter().run()),
        tableMenuItem('fa-trash-alt',      __('editor.table_row_delete'),     () => editor.chain().focus().deleteRow().run()),
        makeMenuSep(),
        tableMenuItem('fa-arrow-left',     __('editor.table_col_before'),     () => editor.chain().focus().addColumnBefore().run()),
        tableMenuItem('fa-arrow-right',    __('editor.table_col_after'),      () => editor.chain().focus().addColumnAfter().run()),
        tableMenuItem('fa-trash-alt',      __('editor.table_col_delete'),     () => editor.chain().focus().deleteColumn().run()),
        makeMenuSep(),
        tableMenuItem('fa-times-circle',   __('editor.table_delete'),         () => editor.chain().focus().deleteTable().run()),
    ]
    const tableDd = makeDropdown('fa-table', __('editor.table'), tableMenuItems)
    dropdown(tableDd)
    activeButtons.push({ el: tableDd._dropdownBtn, getActive: () => editor.isActive('table') })
    sep()

    btn('fa-link', __('editor.link'), () => {
        const existing = editor.getAttributes('link').href || ''
        const { from, to } = editor.state.selection
        const selected = editor.state.doc.textBetween(from, to, '')
        const url = prompt(__('editor.url_link') + ':', existing || selected)
        if (!validateUrl(url)) return
        if (selected || existing) {
            editor.chain().focus().extendMarkRange('link').setLink({ href: url, target: null }).run()
        } else {
            const placeholder = __('editor.link_text')
            editor.chain().focus()
                .insertContent(`<a href="${url}">${placeholder}</a>`)
                .run()
            const { from } = editor.state.selection
            editor.commands.setTextSelection({ from: from - placeholder.length, to: from })
        }
    }, () => editor.isActive('link'))

    btn('fa-image', __('editor.image'), async () => {
        const url = prompt(__('editor.url_image') + ':')
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
            notyf.warning(__('editor.image_not_found'))
        }
    })

    // Кнопка загрузки файла (только если есть data-relate-type)
    if (textarea.dataset.relateType) {
        btn('fa-cloud-arrow-up', __('editor.upload_image'), () => {
            const input = document.createElement('input')
            input.type = 'file'
            input.accept = 'image/*'
            input.onchange = async () => {
                const file = input.files[0]
                if (!file) return
                await uploadImageFn(editor, file)
            }
            input.click()
        })
    }

    btn('fa-play-circle', __('editor.video'), () => {
        const url = prompt(__('editor.url_video') + ':')
        if (!validateUrl(url)) return
        editor.chain().focus().insertVideo(url).run()
    })

    btn('fa-music', __('editor.audio'), () => {
        const url = prompt(__('editor.url_audio') + ':')
        if (!validateUrl(url)) return
        editor.chain().focus().insertAudio(url).run()
    })
    sep()

    btn('fa-plus-square', __('editor.spoiler'), () => {
        if (editor.isActive('spoiler')) {
            editor.chain().focus().lift('spoiler').run()
        } else {
            const title = prompt(__('editor.spoiler_title') + ':', __('editor.spoiler'))
            if (title !== null) editor.chain().focus().insertSpoiler(title || __('editor.spoiler')).run()
        }
    }, () => editor.isActive('spoiler'))
    btn('fa-eye-slash', __('editor.hide'),
        () => editor.isActive('hide')
            ? editor.chain().focus().lift('hide').run()
            : editor.chain().focus().insertHide().run(),
        () => editor.isActive('hide'))
    btn('fa-quote-right', __('editor.quote'), () => {
        if (editor.isActive('blockquote')) {
            editor.chain().focus().toggleBlockquote().run()
        } else {
            const author = prompt(__('editor.quote_author') + ':')
            if (author !== null) editor.chain().focus().toggleBlockquote(author || null).run()
        }
    }, () => editor.isActive('blockquote'))
    btn('fa-code', __('editor.code_block'),
        () => editor.chain().focus().toggleCodeBlock().run(),
        () => editor.isActive('codeBlock'))
    sep()

    dropdown(makeStickerPicker(editor))
    sep()

    btn('fa-eraser', __('editor.clear_format'),
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
    const rows = parseInt(textarea.getAttribute('rows'))
    if (rows) {
        const height = rows * 24 + 22
        editorEl.style.minHeight = height + 'px'
        editorEl.style.height = height + 'px'
    }
    wrapper.appendChild(editorEl)

    textarea.style.display = 'none'
    textarea.removeAttribute('required')
    textarea.removeAttribute('maxlength') // maxlength по HTML бессмысленен — считаем текст

    if (textarea.closest('.is-invalid')) {
        wrapper.classList.add('is-invalid')
    }

    let isChanged = false

    // === Image Upload Helper ===
    async function uploadImage(editor, file, pos = null) {
        // Проверка размера файла (10MB максимум)
        const maxSize = 10 * 1024 * 1024
        if (file.size > maxSize) {
            notyf.error(__('editor.upload_failed') + ': файл слишком большой')
            return
        }

        // Проверка типа файла
        if (!file.type.startsWith('image/')) {
            notyf.error(__('editor.upload_failed') + ': неподдерживаемый формат')
            return
        }

        const formData = new FormData()
        formData.append('file', file)

        const type = textarea.dataset.relateType || null
        const id = textarea.dataset.relateId || 0
        if (type) formData.append('type', type)
        if (id) formData.append('id', id)

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content

        try {
            const response = await fetch('/ajax/file/upload', {
                method: 'POST',
                body: formData,
                headers: { 'X-CSRF-TOKEN': csrfToken }
            })

            const data = await response.json()

            if (data.success && data.path) {
                editor.chain().focus().insertContentAt(pos ?? editor.state.selection.from, {
                    type: 'image',
                    attrs: { src: data.source || data.path },
                }).run()

                const scope = textarea.closest('form') ?? document
                const templateEl = scope.querySelector('.js-image-template')
                const template = templateEl?.cloneNode(true)
                if (template) {
                    const img = template.querySelector('img')
                    if (img) {
                        img.setAttribute('src', data.path)
                        img.setAttribute('data-source', data.source || data.path)
                    }
                    template.querySelector('a')?.setAttribute('data-id', data.id)
                    scope.querySelector('.js-files')?.insertAdjacentHTML('beforeend', template.innerHTML)
                }
            } else {
                notyf.error(data.message || __('editor.upload_failed'))
            }
        } catch (error) {
            notyf.error(__('editor.upload_error'))
        }
    }
    // ============================

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
                codeBlock: { HTMLAttributes: { class: 'code' } },
            }),
            Blockquote,
            TextStyle,
            Color,
            BackgroundColor,
            FontSize,
            TextAlign.configure({ types: ['heading', 'paragraph'] }),
            Image.configure({ inline: false, HTMLAttributes: { class: 'image' }, allowBase64: false }),
            FileHandler.configure({
                allowedMimeTypes: ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp', 'image/svg+xml'],
                onDrop: async (editor, files, pos) => {
                    for (const file of files) {
                        await uploadImage(editor, file, pos)
                    }
                },
                onPaste: async (editor, files, htmlContent) => {
                    for (const file of files) {
                        await uploadImage(editor, file)
                    }
                },
            }),
            Placeholder.configure({ placeholder }),
            VideoEmbed,
            AudioEmbed,
            Spoiler,
            Hide,
            Sticker,
            Table.configure({ resizable: false, HTMLAttributes: { class: 'table' } }),
            TableRow,
            TableHeader,
            TableCell,
            CharacterCount,
            Mention.configure({
                HTMLAttributes: { class: 'user' },
                renderHTML({ options, node }) {
                    return ['a', mergeAttributes(options.HTMLAttributes, { href: '/users/' + node.attrs.id }), '@' + node.attrs.id]
                },
                suggestion,
            }),
        ],
        content: textarea.value || '',
        onUpdate({ editor }) {
            textarea.value = fixNewlines(rgbToHex(editor.getHTML()))
            isChanged = true
            updateCounter()
        },
        onCreate({ editor }) {
            textarea.value = fixNewlines(rgbToHex(editor.getHTML()))
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

    editor.resetChanged  = () => { isChanged = false }
    editor.getIsChanged  = () => isChanged

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

    if (textarea.id) {
        window._tiptapEditors = window._tiptapEditors || {}
        window._tiptapEditors[textarea.id] = editor
    }

    const toolbar = buildToolbar(editor, textarea, uploadImage)
    wrapper.insertBefore(toolbar, editorEl)

    function getCharCount() {
        let count = editor.storage.characterCount.characters()
        editor.state.doc.descendants(node => {
            if (node.type.name === 'spoiler') count += (node.attrs.title || '').length
        })
        return count
    }

    const counterEl = textarea.parentNode.querySelector('.js-textarea-counter')
    function updateCounter() {
        if (!counterEl) return
        const len = getCharCount()
        if (maxLength) {
            const remaining = maxLength - len
            counterEl.textContent = len === 0 ? '' : __('characters_left') + ': ' + remaining
            counterEl.classList.toggle('text-danger', remaining < 0)
        } else {
            counterEl.textContent = len || ''
        }
    }
    updateCounter()

    if (wasRequired) {
        const form = textarea.closest('form')
        if (form) {
            form.addEventListener('submit', e => {
                if (getCharCount() === 0) {
                    e.preventDefault()
                    wrapper.classList.add('is-invalid')
                    wrapper.scrollIntoView({ behavior: 'smooth', block: 'center' })
                    notyf.error(__('validator.empty_field'))
                } else {
                    wrapper.classList.remove('is-invalid')
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
