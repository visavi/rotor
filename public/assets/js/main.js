$(function(){
    var currentLang = $('html').attr('lang');
    translate = window['translate_' + currentLang];

    prettyPrint();
    bootbox.setDefaults({ locale: 'ru' });

    toastr.options = {
        'toastClass' : 'toastr',
        "progressBar": true,
        "positionClass": "toast-top-full-width"
    };

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

    $('[data-toggle="tooltip"]').tooltip();
    $('[data-toggle="popover"]').popover();

    // Hide popover poppers anywhere
    $('body').on('click', function (e) {
        //did not click a popover toggle or popover
        if ($(e.target).data('toggle') !== 'popover'
            && $(e.target).parents('.popover.in').length === 0) {
            $('[data-toggle="popover"]').popover('hide');
        }
    });

    // Spoiler
    $('.spoiler-title').on('click', function() {
        var spoiler = $(this).parent();
        spoiler.toggleClass('spoiler-open');
        spoiler.find('.spoiler-text:first').slideToggle();
    });

    /* Show news on the main */
    $('.news-title').on('click', function() {
        $(this).toggleClass('fa-rotate-180');
        $(this).nextAll(".news-text:first").slideToggle();
    });

    $('a.gallery').colorbox({
        maxWidth: '100%',
        maxHeight: '100%',
        onComplete : function() {
            $(this).colorbox.resize();
        }
    }).colorbox({rel: function() {
            return $(this).data('group');
        },
        current: translate.photo_count
    });

    $(window).resize(function() {
        $.colorbox.resize();
    });

    $('.colorpick').colorpicker({
        useAlpha: false,
        format: 'hex'
    });

    $('.carousel').carousel({
        interval: false
    });

    $('.phone').mask('0 000 000-00-00');
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
        }, 600);
        return false;
    });


 /*   if ($('.markItUpEditor').val().length > 0) {
        window.onbeforeunload = function() {
            return "You're about to end your session, are you sure?";
        }
    }*/

    $('.js-messages-block').on('show.bs.dropdown', function () {
        getNewMessages();
    })
});

/* Вывод уведомлений */
notification = function(type, title, message, optionsOverride)
{
    return toastr[type](message, title, optionsOverride);
};

/* Показ формы загрузки файла */
showAttachForm = function()
{
    $('.js-attach-button').hide();
    $('.js-attach-form').slideDown();

    return false;
};

/* Переход к форме ввода */
postJump = function()
{
    $('html, body').animate({
        scrollTop: ($('.section-form').offset().top)
    }, 500);
};

/* Ответ на сообщение */
postReply = function(el)
{
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
postQuote = function(el)
{
    postJump();

    var field   = $('.markItUpEditor');
    var post    = $(el).closest('.section');
    var author  = post.find('.section-author').data('login');
    var date    = post.find('.section-date').text();
    var text    = post.find('.section-message').clone();
    var message = text.find('blockquote').remove().end().text();

    var $lastSymbol = field.val().slice(field.val().length - 1);
    var separ = $.inArray($lastSymbol, ['', '\n']) !== -1 ? '' : '\n';

    field.focus().val(field.val() + separ + '[quote=' + author + ' ' + date + ']' + $.trim(message) + '[/quote]\n');

    return false;
};

/* Выход с сайта */
logout = function(el)
{
    bootbox.confirm(translate.confirm_logout, function(result) {
        if (result) {
            window.location = $(el).attr("href");
        }
    });

    return false;
};

/* Отправка жалобы на спам */
sendComplaint = function(el)
{
    bootbox.confirm(translate.confirm_complain_submit, function(result) {
        if (result) {
            $.ajax({
                data: {
                    id: $(el).data('id'),
                    type: $(el).data('type'),
                    page: $(el).data('page'),
                    token: $(el).data('token')
                },
                dataType: 'json', type: 'post', url: '/ajax/complaint',
                success: function(data) {

                    $(el).replaceWith('<i class="fa fa-bell-slash text-muted"></i>');

                    if (data.status === 'error') {
                        notification('error', data.message);
                        return false;
                    }

                    if (data.status === 'success') {
                        notification('success', translate.complain_submitted);
                    }
                }
            });
        }
    });

    return false;
};

/* Добавление или удаление закладок */
bookmark = function(el)
{
    $.ajax({
        data: {
            tid: $(el).data('tid'),
            token: $(el).data('token')
        },
        dataType: 'json', type: 'post', url: '/forums/bookmarks/perform',
        success: function(data) {

            if (data.status === 'error') {
                notification('error', data.message);
                return false;
            }

            if (data.status === 'added') {
                notification('success', data.message);
                $(el).text($(el).data('from'));
            }

            if (data.status === 'deleted') {
                notification('success', data.message);
                $(el).text($(el).data('to'));
            }
        }
    });

    return false;
};

/* Удаление сообщения в форуме */
deletePost = function(el)
{
    $.ajax({
        data: {tid: $(el).data('tid'), token: $(el).data('token')},
        dataType: 'json', type: 'post', url: '/forums/active/delete',
        success: function(data) {

            if (data.status === 'error') {
                notification('error', data.message);
                return false;
            }

            if (data.status === 'success') {
                notification('success', translate.message_deleted);
                $(el).closest('.section').hide('slow');
            }
        }
    });

    return false;
};

/* Удаление комментариев */
deleteComment = function(el)
{
    bootbox.confirm(translate.confirm_message_delete, function(result) {
        if (result) {
            $.ajax({
                data: {
                    id: $(el).data('id'),
                    rid: $(el).data('rid'),
                    type: $(el).data('type'),
                    token: $(el).data('token')
                },
                dataType: 'json', type: 'post', url: '/ajax/delcomment',
                success: function(data) {

                    if (data.status === 'error') {
                        notification('error', data.message);
                        return false;
                    }

                    if (data.status === 'success') {
                        notification('success', translate.message_deleted);
                        $(el).closest('.section').hide('slow');
                    }
                }
            });
        }
    });

    return false;
};

/* Изменение рейтинга */
changeRating = function(el)
{
    $.ajax({
        data: {
            id: $(el).data('id'),
            type: $(el).data('type'),
            vote: $(el).data('vote'),
            token: $(el).data('token')
        },
        dataType: 'json',
        type: 'post',
        url: '/ajax/rating',
        success: function(data) {
            if (data.status === 'error') {
                return false;
            }

            if (data.status === 'success') {
                rating = $(el).closest('.js-rating').find('b');

                $(el).closest('.js-rating').find('a').removeClass('active');

                if (! data.cancel) {
                    $(el).addClass('active');
                }

                rating.html($(data.rating));
            }
        }
    });

    return false;
};

/**
 * Удаляет запись из истории рейтинга
 */
deleteRating = function(el)
{
    bootbox.confirm(translate.confirm_message_delete, function(result) {
        if (result) {
            $.ajax({
                data: {
                    id: $(el).data('id'),
                    token: $(el).data('token')
                },
                dataType: 'json', type: 'post', url: '/ratings/delete',
                success: function(data) {

                    if (data.status === 'error') {
                        notification('error', data.message);
                        return false;
                    }

                    if (data.status === 'success') {
                        notification('success', translate.record_deleted);
                        $(el).closest('.section').hide('slow');
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
deleteSpam = function(el)
{
    $.ajax({
        data: {id: $(el).data('id'), token: $(el).data('token')},
        dataType: 'json', type: 'post', url: '/admin/spam/delete',
        success: function(data) {

            if (data.status === 'error') {
                notification('error', data.message);
                return false;
            }

            if (data.status === 'success') {
                notification('success', translate.record_deleted);
                $(el).closest('.section').hide('slow');
            }
        }
    });

    return false;
};

/**
 * Удаляет запись со стены сообщений
 */
deleteWall = function(el)
{
    bootbox.confirm(translate.confirm_message_delete, function(result) {
        if (result) {
            $.ajax({
                data: {id: $(el).data('id'), login: $(el).data('login'), token: $(el).data('token')},
                dataType: 'json', type: 'post', url: '/walls/' + $(el).data('login') + '/delete',
                success: function (data) {

                    if (data.status === 'error') {
                        notification('error', data.message);
                        return false;
                    }

                    if (data.status === 'success') {
                        notification('success', translate.record_deleted);
                        $(el).closest('.section').hide('slow');
                    }
                }
            });
        }
    });

    return false;
};

/* Показ формы создания голосования */
showVoteForm = function()
{
    $('.js-vote-form').toggle();

    return false;
};

/* Копирует текст в input */
copyToClipboard = function(el)
{
    var form = $(el).closest('.input-group');
    form.find('input').select();

    form.find('.input-group-append')
        .attr('data-original-title', translate.copied)
        .tooltip('update')
        .tooltip('show');

    document.execCommand("copy");

    return false;
};

/* Загрузка изображения */
submitImage = function(el, paste)
{
    var form = new FormData();
    form.append('image', el.files[0]);
    form.append('id', $(el).data('id'));
    form.append('type', $(el).data('type'));
    form.append('token', $(el).data('token'));

    $.ajax({
        data: form,
        type: 'post',
        contentType: false,
        processData: false,
        dataType: 'json',
        url: '/ajax/image/upload',
        beforeSend: function() {
            $('.js-images').append('<i class="fas fa-spinner fa-spin fa-3x mx-3"></i>');
        },
        complete: function() {
            $('.fa-spinner').remove();
        },
        success: function(data) {

            if (data.status === 'error') {
                notification('error', data.message);
                return false;
            }

            if (data.status === 'success') {
                var template = $('.js-image-template').clone();

                template.find('img').attr({
                    'src'         : data.path,
                    'data-source' : data.source
                });

                template.find('a').attr('data-id', data.id);

                $('.js-images').append(template.html());

                if (paste) {
                    pasteImage(template.find('img'));
                }
            }
        }
    });

    return false;
};

/* Вставка изображения в поле */
pasteImage = function(el)
{
    var field    = $('.markItUpEditor');
    var caretPos = field[0].selectionStart;
    var text     = field.val();
    var paste    = '[img]' + $(el).data('source') + '[/img]';
    field.focus().val(text.substring(0, caretPos) + paste + text.substring(caretPos));
};

/* Удаление изображения */
deleteImage = function(el)
{
    $.ajax({
        data: {
            id: $(el).data('id'),
            type: $(el).data('type'),
            token: $(el).data('token')
        },
        dataType: 'json',
        type: 'post',
        url: '/ajax/image/delete',
        success: function(data) {

            if (data.status === 'error') {
                notification('error', data.message);
                return false;
            }

            if (data.status === 'success') {
                $(el).closest('.js-image').hide('fast');
            }
        }
    });

    return false;
};

/* Показывает форму для повторной отправки кода подтверждения */
resendingCode = function()
{
    $('.js-resending-link').hide();
    $('.js-resending-form').show();

    return false;
};

/* Показывает панель с запросами */
showQueries = function() {
    $('.js-queries').slideToggle();
};

/* Get new messages */
getNewMessages = function() {
    $.ajax({
        dataType: 'json',
        type: 'get',
        url: '/messages/new',
        success: function(data) {
            if (data.status === 'error') {
                return false;
            }

            if (data.status === 'success') {
                $('.js-messages-block').find('.js-messages').empty().append(data.messages);
            }
        }
    });

    return false;
};
