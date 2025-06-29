$(function () {
    let html = $('html');
    let currentLang = html.attr('lang');
    translate = window['translate_' + currentLang];

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
        OK : translate.buttons.ok,
        CANCEL : translate.buttons.cancel,
        CONFIRM : translate.buttons.ok,
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

    $('.markItUp').markItUp(mySettings).on('input', function() {
        const $this = $(this);
        const maxlength = $this.attr('maxlength');
        const text = $this.val().replace(/(\r\n|\n|\r)/g, "\r\n");
        const currentLength = text.length;
        const $counter = $('.js-textarea-counter');

        $counter.toggleClass('text-danger', currentLength > maxlength);

        const remaining = maxlength - currentLength;
        $counter.text(currentLength === 0 ? '' : translate.characters_left + ': ' + remaining);
    });

    $('.markItUpHtml').markItUp(myHtmlSettings);

    $('[data-bs-toggle="tooltip"]').tooltip();
    $('[data-bs-toggle="popover"]').popover();

    /* Hide popover poppers anywhere */
    $('body').on('click', function (e) {
        //did not click a popover toggle or popover
        if ($(e.target).data('bs-toggle') !== 'popover'
            && $(e.target).parents('.popover.in').length === 0) {
            $('[data-bs-toggle="popover"]').popover('hide');
        }
    });

    /* Spoiler */
    $('.spoiler-title').on('click', function () {
        let spoiler = $(this).parent();
        spoiler.toggleClass('spoiler-open');
        spoiler.find('.spoiler-text:first').slideToggle();
    });

    /* Show news on the main */
    $('.news-title').on('click', function () {
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
        $('#theme-icon-active').attr('class', `fa-solid ${icon} fa-lg`);

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
});

/* Показ формы загрузки файла */
showAttachForm = function () {
    $('.js-attach-button').hide();
    $('.js-attach-form').slideDown();

    return false;
};

/* Переход к форме ввода */
postJump = function () {
    $('html, body').animate({
        scrollTop: ($('.section-form').offset().top - 50)
    }, 100);
};

/* Ответ на сообщение */
postReply = function (el) {
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
postQuote = function (el) {
    postJump();

    let field   = $('.markItUpEditor');
    let post    = $(el).closest('.section');
    let author  = post.find('.section-author').data('login');
    let date    = post.find('.section-date').text();
    let text    = post.find('.section-message').clone();
    let message = $.trim(text.find('blockquote').remove().end().text());

    let $lastSymbol = field.val().slice(field.val().length - 1);
    let separ = $.inArray($lastSymbol, ['', '\n']) !== -1 ? '' : '\n';

    if (! message) {
        field.focus().val(field.val() + separ + author + ', ');

        return false;
    }

    field.focus().val(field.val() + separ + '[quote=' + author + ' ' + date + ']' + message + '[/quote]\n');

    return false;
};

/* Выход с сайта */
logout = function (el) {
    bootbox.confirm(translate.confirm_logout, function (result) {
        if (result) {
            window.location = $(el).attr("href");
        }
    });

    return false;
};

/* Отправка жалобы на спам */
sendComplaint = function (el) {
    bootbox.confirm(translate.confirm_complain_submit, function (result) {
        if (result) {
            $.ajax({
                data: {
                    id: $(el).data('id'),
                    type: $(el).data('type'),
                    page: $(el).data('page'),
                    _token: $(el).data('token')
                },
                dataType: 'json', type: 'post', url: '/ajax/complaint',
                success: function (data) {

                    $(el).replaceWith('<i class="fa fa-bell-slash text-muted"></i>');

                    if (data.success) {
                        toastr.success(translate.complain_submitted);
                    } else {
                        toastr.error(data.message);
                    }
                }
            });
        }
    });

    return false;
};

/* Добавление или удаление закладок */
bookmark = function (el) {
    $.ajax({
        data: {
            tid: $(el).data('tid'),
            _token: $(el).data('token')
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
deletePost = function (el) {
    bootbox.confirm(translate.confirm_message_delete, function (result) {
        if (result) {
            $.ajax({
                data: {_token: $(el).data('token'),},
                dataType: 'json', type: 'delete', url: $(el).attr('href'),
                success: function (data) {
                    if (data.success) {
                        toastr.success(data.message);
                        $(el).closest('.section').hide('slow');
                    } else {
                        toastr.error(data.message);
                    }
                }
            });
        }
    });

    return false;
}

/* Удаление комментариев */
deleteComment = function (el) {
    bootbox.confirm(translate.confirm_message_delete, function (result) {
        if (result) {
            $.ajax({
                data: {
                    id: $(el).data('id'),
                    rid: $(el).data('rid'),
                    type: $(el).data('type'),
                    _token: $(el).data('token')
                },
                dataType: 'json', type: 'post', url: '/ajax/delcomment',
                success: function (data) {
                    if (data.success) {
                        toastr.success(translate.message_deleted);
                        $(el).closest('.section').hide('slow');
                    } else {
                        toastr.error(data.message);
                    }
                }
            });
        }
    });

    return false;
};

/* Изменение рейтинга */
changeRating = function (el) {
    $.ajax({
        data: {
            id: $(el).data('id'),
            type: $(el).data('type'),
            vote: $(el).data('vote'),
            _token: $(el).data('token')
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
deleteRating = function (el) {
    bootbox.confirm(translate.confirm_message_delete, function (result) {
        if (result) {
            $.ajax({
                data: {
                    id: $(el).data('id'),
                    _token: $(el).data('token')
                },
                dataType: 'json', type: 'post', url: '/ratings/delete',
                success: (data)=> {
                    if (data.success) {
                        toastr.success(translate.record_deleted);
                        $(el).closest('.section').hide('slow');
                    } else {
                        toastr.error(data.message);
                    }
                }
            });
        }
    });

    return false;
};

/**
 * Удаляет запись из списка жалоб
 */
deleteSpam = function (el) {
    $.ajax({
        data: {id: $(el).data('id'), _token: $(el).data('token')},
        dataType: 'json', type: 'post', url: '/admin/spam/delete',
        success: function (data) {
            if (data.success) {
                toastr.success(translate.record_deleted);
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
deleteWall = function (el) {
    bootbox.confirm(translate.confirm_message_delete, function (result) {
        if (result) {
            $.ajax({
                data: {id: $(el).data('id'), login: $(el).data('login'), _token: $(el).data('token')},
                dataType: 'json', type: 'post', url: '/walls/' + $(el).data('login') + '/delete',
                success: function (data) {
                    if (data.success) {
                        toastr.success(translate.record_deleted);
                        $(el).closest('.section').hide('slow');
                    } else {
                        toastr.error(data.message);
                    }
                }
            });
        }
    });

    return false;
};

/* Копирует текст в input */
copyToClipboard = function (el) {
    let form = $(el).closest('.input-group');
    form.find('input').select();

    form.find('.input-group-text')
        .attr('data-bs-original-title', translate.copied)
        .tooltip('update')
        .tooltip('show');

    document.execCommand("copy");

    return false;
};

/* Загрузка изображения */
submitFile = function (el) {
    const form = new FormData();
    form.append('file', el.files[0]);
    form.append('id', $(el).data('id'));
    form.append('type', $(el).data('type'));
    form.append('_token', $(el).data('token'));

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
submitImage = function (el, paste) {
    const form = new FormData();
    form.append('file', el.files[0]);
    form.append('id', $(el).data('id'));
    form.append('type', $(el).data('type'));
    form.append('_token', $(el).data('token'));

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
pasteImage = function (el) {
    let field = $('.markItUpEditor');
    let paste = '[img]' + $(el).find('img').data('source') + '[/img]';

    field.focus().caret(paste);
};

/* Удаление изображения из формы */
cutImage = function (path) {
    let field = $('.markItUpEditor');

    if (field.length && field.val()) {
        let text = field.val();
        let cut = '[img]' + path + '[/img]';
        field.val(text.replace(cut, ''));
    }
};


/* Удаление файла */
deleteFile = function (el) {
    const id = $(el).data('id');
    const type = $(el).data('type');
    const token = $(el).data('token');

    $.ajax({
        data: { id, type, _token: token },
        dataType: 'json',
        type: 'post',
        url: '/ajax/file/delete',
        success: function (data) {
            if (!data.success) {
                toastr.error(data.message);
                return;
            }

            if (data.path) {
                cutImage(data.path);
            }

            $(el).closest('.js-file').hide('fast');
        },
        error: function (jqXHR, textStatus, errorThrown) {
            toastr.error('Ошибка удаления файла: ' + textStatus);
        }
    });

    return false;
};

/* Показывает форму для повторной отправки кода подтверждения */
resendingCode = function () {
    $('.js-resending-link').hide();
    $('.js-resending-form').show();

    return false;
};

/* Показывает панель с запросами */
showQueries = function () {
    $('.js-queries').slideToggle();
};

/* Get new messages */
getNewMessages = function () {
    const notify_item = $('.js-messages-block .app-nav__item');
    const notify_badge = notify_item.find('.badge');
    const notify_span = $('.app-notification__title span');

    $.ajax({
        dataType: 'json',
        type: 'get',
        url: '/messages/new',
        beforeSend: function () {
            $('.js-messages').append('<li class="js-message-spin text-center"><i class="fas fa-spinner fa-spin fa-2x my-2"></i></li>');
        },
        complete: function () {
            $('.js-message-spin').remove();
        },
        success: function (data) {
            if (data.success) {
                if (notify_badge.length > 0) {
                    notify_badge.html(data.countMessages);
                } else {
                    notify_item.append('<span class="badge bg-notify">' + data.countMessages + '</span>');
                }
                notify_span.html(data.countMessages);
                $('.js-messages-block').find('.js-messages').empty().append(data.dialogues);
            } else {
                if (notify_badge.length > 0) {
                    notify_span.html(0);
                    notify_badge.remove();
                }

                return false;
            }
        }
    });

    return false;
};

/* Инициализирует главное изображение слайдера */
initSlideMainImage = function (el) {
    const mainHref = $(el).attr('href');
    const slider = $(el).closest('.media-file');

    // Исключает дублирующуюся миниатюру
    slider.find('.slide-thumb-link').removeClass('fancybox-exclude');
    slider.find('.slide-thumb-link[href="' + mainHref + '"]').addClass('fancybox-exclude');
};

/* Инициализирует миниатюру слайдера */
initSlideThumbImage = function (e, el) {
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
checkLogin = function (el) {
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
