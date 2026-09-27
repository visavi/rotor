import { ajax } from './ajax.js'

document.addEventListener('DOMContentLoaded', function () {
    const treeviewMenu = document.querySelector('.app-menu')
    const app = document.querySelector('.app')
    const isMobile = () => window.matchMedia('(max-width: 767px)').matches
    // Тема поддерживает режим иконок, и в сайдбаре есть что сворачивать
    const hasMini = app?.hasAttribute('data-sidebar-mini') && document.querySelector('.app-sidebar .menu-icon')
    const isOpen = () => app?.classList.contains('sidenav-toggled')
    const syncScrollLock = () => {
        document.body.style.overflow = isOpen() && isMobile() ? 'hidden' : ''
    }
    const toggleSidebar = (open) => {
        app?.classList.toggle('sidenav-toggled', open)
        syncScrollLock()
    }

    document.querySelectorAll('[data-bs-toggle="sidebar"]').forEach(el => {
        el.addEventListener('click', function (e) {
            e.preventDefault()

            // На десктопе сайдбар сворачивается до иконок, состояние живет в куке
            if (hasMini && ! isMobile()) {
                const mini = app.classList.toggle('sidebar-mini')
                ajax({ type: 'POST', url: '/ajax/set-sidebar', data: { sidebar: mini ? 'mini' : 'full' } })

                return
            }

            toggleSidebar()
        })
    })

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && isOpen() && isMobile()) {
            toggleSidebar(false)
        }
    })

    // Страница уходит в bfcache закрытой и без анимации, иначе «назад» вернет ее с открытым сайдбаром
    window.addEventListener('pagehide', function () {
        if (isOpen()) {
            toggleSidebar(false)
            document.getAnimations?.().forEach(a => a instanceof CSSTransition && a.finish())
        }
    })

    window.addEventListener('resize', syncScrollLock)

    document.querySelectorAll('.app-sidebar [data-bs-toggle="treeview"]').forEach(el => {
        el.addEventListener('click', function (e) {
            // В свернутом сайдбаре подменю раскрывается наведением, клик ведет по ссылке
            if (app?.classList.contains('sidebar-mini')) {
                return
            }

            e.preventDefault()
            const parent = el.parentElement

            if (!parent.classList.contains('is-expanded')) {
                treeviewMenu?.querySelectorAll('[data-bs-toggle="treeview"]').forEach(item => {
                    item.parentElement.classList.remove('is-expanded')
                })
            }

            parent.classList.toggle('is-expanded')
        })
    })
})
