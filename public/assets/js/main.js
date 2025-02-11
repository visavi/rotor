$(function () {
    let html = $('html');
    let currentLang = html.attr('lang');
    translate = window['translate_' + currentLang];

    prettyPrint();
    tags.init(".input-tag", {
        allowNew: true,
        server: "/blogs/tags/search",
        liveServer: true,
        clearEnd: true,
        allowClear: true,
        suggestionsThreshold: 2,
        max: 5,
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

    fancybox.bind("[data-fancybox]", {
        // Your custom options
    });

    $('.markItUp').markItUp(mySettings).on('input', function () {
        var maxlength = $(this).attr('maxlength');
        var text      = $(this).val().replace(/(\r\n|\n|\r)/g, "\r\n");

        var currentLength = text.length;
        var counter = $('.js-textarea-counter');

        if (currentLength > maxlength) {
            counter.addClass('text-danger');
        } else {
            counter.removeClass('text-danger');
        }

        counter.text(translate.characters_left + ': ' + (maxlength - currentLength));

        if (currentLength === 0) {
            counter.empty();
        }
    });

    $('.markItUpHtml').markItUp(myHtmlSettings);

    $('[data-bs-toggle="tooltip"]').tooltip();
    $('[data-bs-toggle="popover"]').popover();

    // Hide popover poppers anywhere
    $('body').on('click', function (e) {
        //did not click a popover toggle or popover
        if ($(e.target).data('bs-toggle') !== 'popover'
            && $(e.target).parents('.popover.in').length === 0) {
            $('[data-bs-toggle="popover"]').popover('hide');
        }
    });

    // Spoiler
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

    /*$('.carousel').carousel({
        interval: false
    });*/

    $('.phone').mask('0 000 000-00-00-00');
    $('.birthday').mask('00.00.0000');

    // Scroll up
    $(window).scroll(function () {
        if ($(this).scrollTop() > 200) {
            $('.scrollup').fadeIn();
        } else {
            $('.scrollup').fadeOut();
        }
    });

    $('.scrollup').click(function () {
        $("html, body").animate({
            scrollTop: 0
        }, 100);
        return false;
    });

    /*if ($('.markItUpEditor').val().length > 0) {
        window.onbeforeunload = function () {
            return "You're about to end your session, are you sure?";
        }
    }*/

    $('.js-messages-block').on('show.bs.dropdown', function () {
        getNewMessages();
    })

    $('[data-bs-theme-value]').click(function() {
        let currentTheme = $(this).data('bs-theme-value');
        let activeThemeClass = $(this).find('i').attr('class');

        $('html').attr('data-bs-theme', currentTheme);

        $('[data-bs-theme-value]').removeClass('active');
        $(this).addClass('active');
        $('#theme-icon-active').attr('class', activeThemeClass);

        $.ajax({
            type: 'POST',
            url: '/ajax/set-theme',
            data: {
                theme: currentTheme,
            }
        });
    });

    let theme = html.data('bs-theme');
    let currentTheme = $("[data-bs-theme-value='" + theme + "']");
    let activeThemeClass = currentTheme.find('i').attr('class');

    currentTheme.addClass('active');
    $('#theme-icon-active').attr('class', activeThemeClass);
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
        scrollTop: ($('.section-form').offset().top)
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

/* Показ формы создания голосования */
showVoteForm = function () {
    $('.js-vote-form').toggle();

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
    let text  = field.val();
    let cut   = '[img]' + path + '[/img]';

    field.val(text.replace(cut, ''));
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

            cutImage(data.path);
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

let checkTimeout;
/* Проверка логина */
checkLogin = function (el) {
    const block = $(el).closest('.mb-3');
    const message = block.find('.invalid-feedback');

    if ($(el).val().length < 3) {
        block.removeClass('is-valid is-invalid');
        message.empty();

        return false;
    }

    clearTimeout(checkTimeout);

    checkTimeout = setTimeout(function () {
        $.ajax({
            data: {
                login: $(el).val()
            },
            dataType: 'json',
            type: 'post',
            url: '/check-login',
            success: function (data) {
                if (data.success) {
                    block.removeClass('is-invalid').addClass('is-valid');
                    message.empty();
                } else {
                    block.removeClass('is-valid').addClass('is-invalid');
                    message.text(data.message)
                }
            }
        });
    }, 1000);

    return false;
};
