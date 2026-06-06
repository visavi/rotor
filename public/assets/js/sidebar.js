document.addEventListener('DOMContentLoaded', function () {
    const treeviewMenu = document.querySelector('.app-menu')
    const app = document.querySelector('.app')
    const isMobile = () => window.matchMedia('(max-width: 767px)').matches
    const syncScrollLock = () => {
        document.body.style.overflow = app?.classList.contains('sidenav-toggled') && isMobile() ? 'hidden' : ''
    }

    document.querySelectorAll('[data-bs-toggle="sidebar"]').forEach(el => {
        el.addEventListener('click', function (e) {
            e.preventDefault()
            app?.classList.toggle('sidenav-toggled')
            syncScrollLock()
        })
    })

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && app?.classList.contains('sidenav-toggled') && isMobile()) {
            app.classList.remove('sidenav-toggled')
            syncScrollLock()
        }
    })

    window.addEventListener('resize', syncScrollLock)

    document.querySelectorAll('.app-sidebar [data-bs-toggle="treeview"]').forEach(el => {
        el.addEventListener('click', function (e) {
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
