export const suggestion = {
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
