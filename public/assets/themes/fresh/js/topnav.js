import { Dropdown } from 'bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    // Дропдауны с position:fixed чтобы не обрезались overflow родителя
    document.querySelectorAll('.app-topnav [data-bs-toggle="dropdown"]').forEach((el) => {
        new Dropdown(el, {
            popperConfig: (config) => ({ ...config, strategy: 'fixed' }),
        });
    });

    // Drag-scroll для горизонтального меню
    const nav = document.querySelector('.app-topnav');
    if (!nav) return;

    const inner = nav.querySelector('.app-topnav__inner');
    let startX, scrollLeft, isDragging = false;

    function updateFade() {
        const max = inner.scrollWidth - inner.clientWidth;
        nav.classList.toggle('has-scroll-left', inner.scrollLeft > 0);
        nav.classList.toggle('has-scroll-right', inner.scrollLeft < max - 1);
    }

    inner.addEventListener('scroll', updateFade);
    updateFade();

    nav.addEventListener('mousedown', (e) => {
        if (e.button !== 0) return;
        startX = e.pageX;
        scrollLeft = inner.scrollLeft;
        isDragging = false;
        nav.style.cursor = 'grabbing';
        e.preventDefault();
    });

    document.addEventListener('mousemove', (e) => {
        if (!startX) return;
        const walk = e.pageX - startX;
        if (!isDragging && Math.abs(walk) > 3) {
            isDragging = true;
            nav.style.pointerEvents = 'none';
        }
        inner.scrollLeft = scrollLeft - walk;
    });

    document.addEventListener('mouseup', () => {
        if (!startX) return;
        nav.style.cursor = '';
        startX = null;
        if (isDragging) {
            setTimeout(() => {
                nav.style.pointerEvents = '';
                isDragging = false;
            }, 0);
        }
    });
});
