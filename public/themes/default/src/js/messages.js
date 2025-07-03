$(document).ready(function () {
    const INTERVAL = 60000;
    const SOUND = new Audio('/assets/ding.mp3');
    const $item = $('.js-messages-block .app-nav__item');
    const originalTitle = document.title.trim();

    function updateBadge(data) {
        if (!$item.length || !data?.success || data.countMessages <= 0) return;

        const count = data.countMessages;
        const badge = $item.find('.badge');

        badge.length ? badge.text(count) : $item.append(`<span class="badge bg-notify">${count}</span>`);

        const prevCount = parseInt(localStorage.getItem('messageCount') || '0');

        if (count > prevCount && !document.hidden) {
            SOUND.play().catch(() => {});
            document.title = `🔴 ${originalTitle}`;
        }

        localStorage.setItem('messageCount', count);
    }

    $(window).on('storage', e => {
        if (e.originalEvent.key === 'messageData') {
            updateBadge(JSON.parse(e.originalEvent.newValue));
        }
    });

    setInterval(() => {
        const now = Date.now();
        const lastReq = parseInt(localStorage.getItem('messageTime') || '0');

        if (now - lastReq < INTERVAL - 100) return;

        localStorage.setItem('messageTime', now);

        $.get('/messages/new').then(data => {
            localStorage.setItem('messageData', JSON.stringify(data));
            updateBadge(data);
        });
    }, INTERVAL);
});
