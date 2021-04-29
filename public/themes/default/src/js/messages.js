const INTERVAL_RELOAD_TIME = 60000;

$(document).ready(function () {
    if ($('.dropdown.js-messages-block')) {
        setInterval(function () {
            $.get('/messages/new').then((res) => {
                const notify_item = $('.js-messages-block .app-nav__item');
                const notify_badge = notify_item.find('.badge');
                const data = JSON.parse(res);

                if (data.status === 'success') {
                    if (notify_badge.length > 0) {
                        notify_badge.html(data.countMessages);
                    } else {
                        notify_item.append('<span class="badge badge-notify">' + data.countMessages + '</span>');
                    }
                } else if (data.status === 'error') {
                    if (notify_badge.length > 0) {
                        notify_badge.remove();
                    }
                }
            });
        }, INTERVAL_RELOAD_TIME);
    }
});