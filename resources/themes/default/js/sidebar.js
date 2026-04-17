document.addEventListener('DOMContentLoaded', function () {
    const treeviewMenu = document.querySelector('.app-menu')

    document.querySelectorAll('[data-bs-toggle="sidebar"]').forEach(el => {
        el.addEventListener('click', function (e) {
            e.preventDefault()
            document.querySelector('.app')?.classList.toggle('sidenav-toggled')
        })
    })

    document.querySelectorAll('[data-bs-toggle="treeview"]').forEach(el => {
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
