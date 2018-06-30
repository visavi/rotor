$(function() {

    prettyPrint();

    bootbox.setDefaults({ locale: 'ru' });

    toastr.options = {
        "progressBar": true,
        "positionClass": "toast-top-full-width"
    };

    $('.markItUp').markItUp(mySettings);
    $('.markItUpHtml').markItUp(myHtmlSettings);

    $('[data-toggle="tooltip"]').tooltip();
    $('[data-toggle="popover"]').popover();

    // Скрывает поповеры по клику в любом месте
    $('body').on('click', function (e) {
        //did not click a popover toggle or popover
        if ($(e.target).data('toggle') !== 'popover'
            && $(e.target).parents('.popover.in').length === 0) {
            $('[data-toggle="popover"]').popover('hide');
        }
    });

    // Спойлер
    $('.spoiler-title').click(function() {
        var spoiler = $(this).parent();
        spoiler.toggleClass('spoiler-open');
        spoiler.find('.spoiler-text:first').slideToggle();
    });

    /* Показ новостей на главной */
    $(".news-title").click(function() {
        $(this).toggleClass('fa-caret-up');
        $(this).nextAll(".news-text:first").slideToggle();
    });

    $('video,audio').mediaelementplayer();

    $("a.gallery").colorbox({
        maxWidth: '100%',
        maxHeight: '100%',
        onComplete : function() {
            $(this).colorbox.resize();
        }
    }).colorbox({rel: function() {
            return $(this).data('group');
        },
        current: 'Фото {current} из {total}'
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
    })
});

/* Вывод уведомлений */
function notify(type, title, message, optionsOverride) {
    return toastr[type](message, title, optionsOverride);
}

/* Показ формы загрузки файла */
function showAttachForm() {
    $('.js-attach-button').hide();
    $('.js-attach-form').slideDown();

    return false;
}

/* Переход к форме ввода */
function postJump() {

    $('html, body').animate({
        scrollTop: ($('.form').offset().top)
    }, 500);
}

/* Ответ на сообщение */
function postReply(el)
{
    postJump();

    var field  = $('.markItUpEditor');
    var post   = $(el).closest('.post');
    var author = post.find('.author').data('login');

    separ = field.val().length ? '\n' : '';
    field.focus().val(field.val() + separ + '@' + author + ', ');

    return false;
}

/* Цитирование сообщения */
function postQuote(el)
{
    postJump();

    var field  = $('.markItUpEditor');
    var post   = $(el).closest('.post');
    var top    = post.find('.b');
    var author = post.find('.author').data('login');
    var date   = top.find('small').text();

    var text    = post.find('.message').clone();
    var message = text.find("blockquote").remove().end().text();

    separ = field.val().length ? '\n' : '';
    field.focus().val(field.val() + separ + '[quote=@' + author + ' ' + date + ']' + $.trim(message) + '[/quote]\n');

    return false;
}

/* Выход с сайта */
function logout(el)
{
    if (bootbox.confirm('Вы уверены, что хотите выйти?', function(result) {
            if (result) {
                window.location = $(el).attr("href");
            }
        }))

        return false;
}

/* Отправка жалобы на спам */
function sendComplaint(el)
{
    bootbox.confirm('Вы действительно хотите отправить жалобу?', function(result) {
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
                        notify('error', data.message);
                        return false;
                    }

                    if (data.status === 'success') {
                        notify('success', 'Жалоба успешно отправлена!');
                    }
                }
            });
        }
    });

    return false;
}

/* Добавление или удаление закладок */
function bookmark(el)
{
    $.ajax({
        data: {tid: $(el).data('tid'), token: $(el).data('token')},
        dataType: 'json', type: 'post', url: '/forums/bookmarks/perform',
        success: function(data) {

            if (data.status === 'error') {
                notify('error', data.message);
                return false;
            }

            if (data.status === 'added') {
                notify('success', data.message);
                $(el).text('Из закладок');
            }

            if (data.status === 'deleted') {
                notify('success', data.message);
                $(el).text('В закладки');
            }
        }
    });

    return false;
}

/* Удаление сообщения в форуме */
function deletePost(el)
{
    $.ajax({
        data: {tid: $(el).data('tid'), token: $(el).data('token')},
        dataType: 'json', type: 'post', url: '/forums/active/delete',
        success: function(data) {

            if (data.status === 'error') {
                notify('error', data.message);
                return false;
            }

            if (data.status === 'success') {
                notify('success', 'Сообщение успешно удалено');

                $(el).closest('.post').hide('slow');
            }
        }
    });

    return false;
}

/* Удаление комментариев */
function deleteComment(el)
{
    bootbox.confirm('Вы действительно хотите удалить комментарий?', function(result) {
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
                        notify('error', data.message);
                        return false;
                    }

                    if (data.status === 'success') {
                        notify('success', 'Комментарий успешно удален!');

                        $(el).closest('.post').hide('slow');
                    }
                }
            });
        }
    });

    return false;
}

/* Изменение рейтинга */
function changeRating(el)
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
                rating = $(el).closest('.js-rating').find('span');

                $(el).closest('.js-rating').find('a').removeClass('active');

                if (! data.cancel) {
                    $(el).addClass('active');
                }

                rating.html($(data.rating));
            }
        }
    });

    return false;
}

/**
 * Удаляет запись из истории рейтинга
 */
function deleteRating(el)
{
    $.ajax({
        data: {
            id: $(el).data('id'),
            token: $(el).data('token')
        },
        dataType: 'json', type: 'post', url: '/ratings/delete',
        success: function(data) {

            if (data.status === 'error') {
                notify('error', data.message);
                return false;
            }

            if (data.status === 'success') {
                notify('success', 'Запись успешно удалена');

                $(el).closest('.post').hide('slow');
            }
        }
    });

    return false;
}

/**
 * Удаляет запись из списка жалоб
 */
function deleteSpam(el)
{
    $.ajax({
        data: {id: $(el).data('id'), token: $(el).data('token')},
        dataType: 'json', type: 'post', url: '/admin/spam/delete',
        success: function(data) {

            if (data.status === 'error') {
                notify('error', data.message);
                return false;
            }

            if (data.status === 'success') {
                notify('success', 'Запись успешно удалена');

                $(el).closest('.post').hide('slow');
            }
        }
    });

    return false;
}

/**
 * Удаляет запись со стены сообщений
 */
function deleteWall(el)
{
    $.ajax({
        data: {id: $(el).data('id'), login: $(el).data('login'), token: $(el).data('token')},
        dataType: 'json', type: 'post', url: '/walls/' + $(el).data('login') + '/delete',
        success: function(data) {

            if (data.status === 'error') {
                notify('error', data.message);
                return false;
            }

            if (data.status === 'success') {
                notify('success', 'Запись успешно удалена');

                $(el).closest('.post').hide('slow');
            }
        }
    });

    return false;
}

/* Показ формы создания голосования */
function showVoteForm()
{
    $('.js-vote-form').toggle();

    return false;
}


function copyToClipboard(el)
{
    var form = $(el).closest('.input-group');
    form.find('input').select();
    document.execCommand("copy");

    return false;
}

/* Загрузка изображения */
function submitImage(el, paste = false)
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
        success: function(data) {

            if (data.status === 'error') {
                notify('error', data.message);
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
}

/* Вставка изображения в поле */
function pasteImage(el)
{
    var field    = $('.markItUpEditor');
    var caretPos = field[0].selectionStart;
    var text     = field.val();
    var paste    = '[img]' + $(el).data('source') + '[/img]';
    field.focus().val(text.substring(0, caretPos) + paste + text.substring(caretPos));
}

/* Удаление изображения */
function deleteImage(el)
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
                notify('error', data.message);
                return false;
            }

            if (data.status === 'success') {
                $(el).closest('.js-image').hide('fast');
            }
        }
    });

    return false;
}
