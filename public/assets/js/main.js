$(function () {
    let body = $('body');

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    prettyPrint();

    tags.init(".input-tag", {
        allowNew: true,
        server: "/blogs/tags-search",
        liveServer: true,
        clearEnd: true,
        allowClear: true,
        suggestionsThreshold: 2,
        max: 10,
        separator: [','],
        addOnBlur: true,
    });

    bootbox.addLocale('my', {
        OK : __('buttons.ok'),
        CANCEL : __('buttons.cancel'),
        CONFIRM : __('buttons.ok'),
    });

    bootbox.setDefaults({
        locale: 'my',
        closeButton: false,
        backdrop: true,
    });

    toastr.options = {
        'toastClass' : 'toastr',
        'progressBar': true,
        'positionClass': 'toast-top-full-width'
    };

    fancybox.bind('[data-fancybox]:not(.fancybox-exclude)', {
        // Your custom options
    });

    $('.markItUp').markItUp(mySettings)
        .on('input', function() {
            const $this = $(this);
            const maxlength = $this.attr('maxlength');
            const text = $this.val().replace(/(\r\n|\n|\r)/g, "\r\n");
            const currentLength = text.length;
            const $counter = $('.js-textarea-counter');

            $counter.toggleClass('text-danger', currentLength > maxlength);

            const remaining = maxlength - currentLength;
            $counter.text(currentLength === 0 ? '' : __('characters_left') + ': ' + remaining);
        })
        .on('markitup:previewUpdated', function() {
            prettyPrint();
        });

    $('.markItUpHtml').markItUp(myHtmlSettings);

    $('[data-bs-toggle="tooltip"]').tooltip();
    $('[data-bs-toggle="popover"]').popover();

    /* Hide popover poppers anywhere */
    body.on('click', function (e) {
        //did not click a popover toggle or popover
        if ($(e.target).data('bs-toggle') !== 'popover'
            && $(e.target).parents('.popover.in').length === 0) {
            $('[data-bs-toggle="popover"]').popover('hide');
        }
    });

    /* Spoiler */
    body.on('click', '.spoiler-title', function () {
        const $spoiler = $(this).closest('.spoiler');
        const $spoilerText = $spoiler.find('.spoiler-text:first');

        $spoiler.toggleClass('spoiler-open');
        $spoilerText.slideToggle();
    });

    /* Show news on the main */
    body.on('click', '.news-title', function () {
        $(this).toggleClass('fa-rotate-180');
        $(this).nextAll(".news-text:first").slideToggle();
    });

    $('.colorpicker').on('input', function () {
        $('.colorpicker-addon').val(this.value);
    });
    $('.colorpicker-addon').on('input', function () {
        $('.colorpicker').val(this.value);
    });

    $('.phone').mask('+0 000 000-00-00-00');
    $('.birthday').mask('00.00.0000');

    /* Scroll up */
    $(window).on('scroll', function() {
        $('.scrollup').stop().fadeTo(200, $(this).scrollTop() > 200 ? 1 : 0);
    });

    $('.scrollup').click(function () {
        $("html, body").animate({
            scrollTop: 0
        }, 100);
        return false;
    });

    /* Уход со страницы */
    let isChanged = false;
    $('.markItUpEditor').on('input change', function() {
        isChanged = true;
    });

    $(window).on('beforeunload', function(e) {
        if (isChanged && $('.markItUpEditor').val().trim().length > 0) {
            e.preventDefault();
            return e.returnValue = '';
        }
    });

    $('form').on('submit', function() {
        $(window).off('beforeunload');
    });

    $('.js-messages-block').on('show.bs.dropdown', function () {
        getNewMessages();
    })

    /* Set theme */
    function setTheme(theme) {
        $('html').attr('data-bs-theme', theme);

        const icon = theme === 'dark' ? 'fa-moon' : 'fa-sun';
        $('#theme-icon-active').attr('class', `fa-regular ${icon} fa-lg`);

        $.ajax({
            type: 'POST',
            url: '/ajax/set-theme',
            data: { theme: theme }
        });
    }

    $('[data-bs-theme-value]').on('click', function() {
        setTheme($(this).data('bs-theme-value'));
    });

    /* Offset при переходе по якорю */
    if (window.location.hash) {
        setTimeout(function() {
            const hash = $(window.location.hash);
            if (hash.length) {
                window.scrollTo(0, hash.offset().top - 100);
            }
        }, 100);
    }

    setTimeout(function() {
        $('.section-content.short-view').each(function() {
            const $el = $(this);
            const el = this;

            // Вычисляем сколько пикселей скрыто
            const hiddenPixels = el.scrollHeight - el.clientHeight;

            if (hiddenPixels > 100) {
                $el.addClass('clamped');

                const $btn = $('<button>')
                    .addClass('btn btn-sm btn-adaptive mt-2')
                    .text('Показать полностью')
                    .on('click', function() {
                        $el.addClass('expanded').removeClass('clamped');
                        $btn.remove();
                    });

                $el.after($btn);
            } else if (hiddenPixels > 0) {
                $el.removeClass('short-view');
            }
        });
    }, 300);
});

/* Показ формы загрузки файла */
window.showAttachForm = function () {
    $('.js-attach-button').hide();
    $('.js-attach-form').slideDown();

    return false;
};

/* Переход к форме ввода */
window.postJump = function () {
    $('html, body').animate({
        scrollTop: ($('.section-form').offset().top - 50)
    }, 100);
};

/* Ответ на сообщение */
window.postReply = function (el) {
    postJump();

    var field  = $('.markItUpEditor');
    var post   = $(el).closest('.section');
    var author = post.find('.section-author').data('login');

    var $lastSymbol = field.val().slice(field.val().length - 1);
    var separ = $.inArray($lastSymbol, ['', '\n']) !== -1 ? '' : '\n';

    field.focus().val(field.val() + separ + author + ', ');

    return false;
};

/* Цитирование сообщения */
window.postQuote = function (el) {
    postJump();

    let field   = $('.markItUpEditor');
    let post    = $(el).closest('.section');
    let author  = post.find('.section-author').data('login');
    let date    = post.find('.section-date').text();
    let text    = post.find('.section-message').clone();
    let message = text.find('blockquote').remove().end().text().trim();

    let $lastSymbol = field.val().slice(field.val().length - 1);
    let separ = $.inArray($lastSymbol, ['', '\n']) !== -1 ? '' : '\n';

    if (! message) {
        field.focus().val(field.val() + separ + author + ', ');

        return false;
    }

    field.focus().val(field.val() + separ + '[quote=' + author + ' ' + date + ']' + message + '[/quote]\n');

    return false;
};

/* Отправка жалобы на спам */
window.sendComplaint = function (el) {
    bootbox.confirm(__('confirm_complain_submit'), function (result) {
        if (!result) return;

        $.ajax({
            data: {
                id: $(el).data('id'),
                type: $(el).data('type'),
                page: $(el).data('page'),
            },
            dataType: 'json', type: 'post', url: '/ajax/complaint',
            success: function (data) {
                $(el).replaceWith('<i class="fa fa-bell-slash text-muted"></i>');

                if (data.success) {
                    toastr.success(__('complain_submitted'));
                } else {
                    toastr.error(data.message);
                }
            }
        });
    });

    return false;
};

/* Добавление или удаление закладок */
window.bookmark = function (el) {
    $.ajax({
        data: {
            tid: $(el).data('tid'),
        },
        dataType: 'json', type: 'post', url: '/forums/bookmarks/perform',
        success: function (data) {

            if (! data.success) {
                toastr.error(data.message);
                return false;
            }

            if (data.success) {
                if (data.type === 'added') {
                    toastr.success(data.message);
                    $(el).text($(el).data('from'));
                }

                if (data.type === 'deleted') {
                    toastr.success(data.message);
                    $(el).text($(el).data('to'));
                }
            }
        }
    });

    return false;
};

/* Удаление записей */
window.deletePost = function (el) {
    bootbox.confirm(__('confirm_message_delete'), function (result) {
        if (! result) return;

        const $el = $(el);
        const url = $el.attr('href');

        $.ajax({
            url: url,
            type: 'delete',
            dataType: 'json',
            success: function (data) {
                if (data.success) {
                    toastr.success(data.message);
                    $el.closest('.section').hide('slow');
                } else {
                    toastr.error(data.message);
                }
            }
        });
    });

    return false;
};

/* Удаление комментариев */
window.deleteComment = function (el) {
    bootbox.confirm(__('confirm_message_delete'), function (result) {
        if (! result) return;

        $.ajax({
            data: {
                id: $(el).data('id'),
                rid: $(el).data('rid'),
                type: $(el).data('type'),
            },
            dataType: 'json', type: 'post', url: '/ajax/delcomment',
            success: function (data) {
                if (data.success) {
                    toastr.success(__('message_deleted'));
                    $(el).closest('.section').hide('slow');
                } else {
                    toastr.error(data.message);
                }
            }
        });
    });

    return false;
};

/* Изменение рейтинга */
window.changeRating = function (el) {
    $.ajax({
        data: {
            id: $(el).data('id'),
            type: $(el).data('type'),
            vote: $(el).data('vote'),
        },
        dataType: 'json',
        type: 'post',
        url: '/ajax/rating',
        success: function (data) {
            if (data.success) {
                const rating = $(el).closest('.js-rating').find('b');

                $(el).closest('.js-rating').find('a').removeClass('active');

                if (! data.cancel) {
                    $(el).addClass('active');
                }

                rating.html($(data.rating));
            } else {
                if (data.message) {
                    toastr.error(data.message);
                }
            }
        }
    });

    return false;
};

/**
 * Удаляет запись из истории рейтинга
 */
window.deleteRating = function (el) {
    bootbox.confirm(__('confirm_message_delete'), function (result) {
        if (! result) return;

        $.ajax({
            data: {
                id: $(el).data('id'),
            },
            dataType: 'json', type: 'post', url: '/ratings/delete',
            success: (data)=> {
                if (data.success) {
                    toastr.success(__('record_deleted'));
                    $(el).closest('.section').hide('slow');
                } else {
                    toastr.error(data.message);
                }
            }
        });
    });

    return false;
};

/**
 * Удаляет запись из списка жалоб
 */
window.deleteSpam = function (el) {
    $.ajax({
        data: {id: $(el).data('id')},
        dataType: 'json', type: 'post', url: '/admin/spam/delete',
        success: function (data) {
            if (data.success) {
                toastr.success(__('record_deleted'));
                $(el).closest('.section').hide('slow');
            } else {
                toastr.error(data.message);
            }
        }
    });

    return false;
};

/**
 * Удаляет запись со стены сообщений
 */
window.deleteWall = function (el) {
    bootbox.confirm(__('confirm_message_delete'), function (result) {
        if (!result) return;

        $.ajax({
            data: {id: $(el).data('id'), login: $(el).data('login')},
            dataType: 'json', type: 'post', url: '/walls/' + $(el).data('login') + '/delete',
            success: function (data) {
                if (data.success) {
                    toastr.success(__('record_deleted'));
                    $(el).closest('.section').hide('slow');
                } else {
                    toastr.error(data.message);
                }
            }
        });
    });

    return false;
};

/* Копирует текст в input */
window.copyToClipboard = function (el) {
    let form = $(el).closest('.input-group');
    form.find('input').select();

    form.find('.input-group-text')
        .attr('data-bs-original-title', __('copied'))
        .tooltip('update')
        .tooltip('show');

    document.execCommand("copy");

    return false;
};

/* Загрузка изображения */
window.submitFile = function (el) {
    const form = new FormData();
    form.append('file', el.files[0]);
    form.append('id', $(el).data('id'));
    form.append('type', $(el).data('type'));

    $.ajax({
        data: form,
        type: 'post',
        contentType: false,
        processData: false,
        dataType: 'json',
        url: '/ajax/file/upload',
        beforeSend: function () {
            $('.js-files').append('<i class="fas fa-spinner fa-spin fa-3x mx-3"></i>');
        },
        complete: function () {
            $('.fa-spinner').remove();
        },
        success: function (data) {
            if (!data.success) {
                toastr.error(data.message);
                return;
            }

            const template = $(data.type === 'image' ? '.js-image-template' : '.js-file-template').clone();

            if (data.type === 'image') {
                template.find('img').attr({
                    'src': data.path,
                    'data-source': data.source
                });
            } else {
                template.find('.js-file-link').attr('href', data.path).text(data.name);
                template.find('.js-file-size').text(data.size);
            }

            template.find('.js-file-delete').attr('data-id', data.id);
            $('.js-files').append(template.html());
        },
        error: function (jqXHR, textStatus, errorThrown) {
            toastr.error('Ошибка загрузки файла: ' + textStatus);
        }
    });

    el.value = '';

    return false;
};

/* Загрузка изображения */
window.submitImage = function (el, paste) {
    const form = new FormData();
    form.append('file', el.files[0]);
    form.append('id', $(el).data('id'));
    form.append('type', $(el).data('type'));

    $.ajax({
        data: form,
        type: 'post',
        contentType: false,
        processData: false,
        dataType: 'json',
        url: '/ajax/file/upload',
        beforeSend: function () {
            $('.js-files').append('<i class="fas fa-spinner fa-spin fa-3x mx-3"></i>');
        },
        complete: function () {
            $('.fa-spinner').remove();
        },
        success: function (data) {
            if (!data.success) {
                toastr.error(data.message);
                return;
            }

            const template = $('.js-image-template').clone();
            template.find('img').attr({
                'src': data.path,
                'data-source': data.source
            });
            template.find('a').attr('data-id', data.id);
            $('.js-files').append(template.html());

            if (paste) {
                pasteImage(template);
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            toastr.error('Ошибка загрузки файла: ' + textStatus);
        }
    });

    el.value = '';

    return false;
};

/* Вставка изображения в форму */
window.pasteImage = function (el) {
    let field = $('.markItUpEditor');
    let paste = '[img]' + $(el).find('img').data('source') + '[/img]';

    field.focus().caret(paste);
};

/* Удаление изображения из формы */
window.cutImage = function (path) {
    let field = $('.markItUpEditor');

    if (field.length && field.val()) {
        let text = field.val();
        let cut = '[img]' + path + '[/img]';
        field.val(text.replace(cut, ''));
    }
};


/* Удаление файла */
window.deleteFile = function (el) {
    bootbox.confirm(__('confirm_file_delete'), function (result) {
        if (!result) return;

        const $el = $(el);
        const id = $el.data('id');
        const type = $el.data('type');

        $.ajax({
            url: '/ajax/file/delete',
            type: 'POST',
            dataType: 'json',
            data: { id, type},
            success: function (data) {
                if (!data.success) {
                    toastr.error(data.message);
                    return;
                }

                if (data.path) {
                    cutImage(data.path);
                }

                $el.closest('.js-file').hide('fast');
            },
            error: function (jqXHR, textStatus) {
                toastr.error('Ошибка удаления файла: ' + textStatus);
            }
        });
    });

    return false;
};

/* Показывает форму для повторной отправки кода подтверждения */
window.resendingCode = function () {
    $('.js-resending-link').hide();
    $('.js-resending-form').show();

    return false;
};

/* Показывает панель с запросами */
window.showQueries = function () {
    $('.js-queries').slideToggle();
};

/* Update message count */
window.updateMessageCount = function (newCount) {
    const data = JSON.parse(localStorage.getItem('messageData') || '{}');
    data.countMessages = parseInt(newCount) || 0;
    localStorage.setItem('messageData', JSON.stringify(data));
    localStorage.setItem('messageCount', newCount);

    window.dispatchEvent(new Event('storage'));
}

/* Get new messages */
window.getNewMessages = function () {
    const $notifyItem = $('.js-messages-block .app-nav__item');
    const $badge = $notifyItem.find('.badge');
    const $titleSpan = $('.app-notification__title span');

    $.ajax({
        dataType: 'json',
        type: 'GET',
        url: '/messages/new',
        beforeSend() {
            $('.js-messages').append('<li class="js-message-spin text-center"><i class="fas fa-spinner fa-spin fa-2x my-2"></i></li>');
        },
        complete() {
            $('.js-message-spin').remove();
        },
        success(data) {
            if (!data?.success) {
                $badge.remove();
                $titleSpan.text(0);
                return;
            }

            const count = data.countMessages;
            const $newBadge = $('<span>', { class: 'badge bg-notify', text: count });

            if ($badge.length) {
                $badge.text(count);
            } else {
                $notifyItem.append($newBadge);
            }

            updateMessageCount(count);

            $titleSpan.text(count);
            $('.js-messages-block .js-messages').empty().append(data.dialogues);
        }
    });

    return false;
};

/* Инициализирует главное изображение слайдера */
window.initSlideMainImage = function (el) {
    const mainHref = $(el).attr('href');
    const slider = $(el).closest('.media-file');

    // Исключает дублирующуюся миниатюру
    slider.find('.slide-thumb-link').removeClass('fancybox-exclude');
    slider.find('.slide-thumb-link[href="' + mainHref + '"]').addClass('fancybox-exclude');
};

/* Инициализирует миниатюру слайдера */
window.initSlideThumbImage = function (e, el) {
    e.preventDefault();

    const newImg = $(el).find('img');
    const imgSource = newImg.data('source');
    const slider = $(el).closest('.media-file');
    const mainLink = slider.find('.slide-main-link');

    // Обновляет главное изображение
    mainLink.fadeOut(50, function() {
        $(this).attr('href', imgSource);
        $(this).find('img').attr('src', newImg.attr('src')).data('source', imgSource);
        $(this).fadeIn(50);
    });

    // Подсветка активной миниатюры
    slider.find('.slide-thumb-image').removeClass('active');
    newImg.addClass('active');
}

let checkTimeout;
/* Проверка логина */
window.checkLogin = function (el) {
    const block = $(el).closest('.mb-3');
    const message = block.find('.invalid-feedback');
    const login = $(el).val().trim();

    if (login.length < 3) {
        block.removeClass('is-valid is-invalid');
        message.empty();

        return;
    }

    clearTimeout(checkTimeout);

    checkTimeout = setTimeout(function () {
        $.ajax({
            url: '/check-login',
            method: 'POST',
            dataType: 'json',
            data: {login: login},
            success: (data) => {
                block.toggleClass('is-valid', data.success);
                block.toggleClass('is-invalid', !data.success);
                message.text(data.success ? '' : data.message);
            },
            error: () => {
                block.removeClass('is-valid').addClass('is-invalid');
            }
        });
    }, 1000);

    return false;
};
