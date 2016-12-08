$(document).ready(function(){

    prettyPrint();

    bootbox.setDefaults({ locale: 'ru' });

    toastr.options = {
        "progressBar": true,
        "positionClass": "toast-top-full-width"
    };

    $('#markItUp').markItUp(mySettings);
    $('#markItUpHtml').markItUp(myHtmlSettings);

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
    $('.spoiler-title').click(function(){
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

    $('a.gallery').colorbox({rel: function(){
        return $(this).data('group');
    },
        current: 'Фото {current} из {total}',
    });
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
function postReply(name)
{
    postJump();

    var field = $("#markItUp");
    separ = field.val().length ? '\n' : '';
    field.focus().val(field.val() + separ + '[b]' + name + '[/b], ');

    return false;
}

/* Цитирование сообщения */
function postQuote(el)
{
    postJump();

    var field = $("#markItUp");
    var post = $(el).closest('.post');
    var author = post.find('b').text();
    var date = post.find('small').text();
    var message = post.find('.message').text();

    separ = field.val().length ? '\n' : '';
    field.focus().val(field.val() + separ + '[quote=' + author + ' ' + date + ']' + message + '[/quote]\n');

    return false;
}

/* Выход с сайта */
function logout(el) {
    if (bootbox.confirm('Вы уверены, что хотите выйти?', function(result){
            if (result) {
                window.location = $(el).attr("href");
            }
        }))

        return false;
}

/* Отправка жалобы на спам */
function sendComplaint(el) {
    bootbox.confirm('Вы действительно хотите отправить жалобу?', function(result){
        if (result) {

            $.ajax({
                data: {id: $(el).data('id'), page: $(el).data('page'), token: $(el).data('token')},
                dataType: 'JSON', type: 'POST', url: $(el).data('type') + '/complaint',
                success: function(data) {

                    $(el).replaceWith('<i class="fa fa-bell-slash-o text-muted"></i>');

                    if (data.status == 'error'){
                        notify('error', data.message);
                        return false;
                    }

                    if (data.status == 'success'){
                        notify('success', 'Жалоба успешно отправлена!');
                    }
                }
            });
        }
    });
    return false;
}

/* Добавление или удаление закладок */
function bookmark(el) {

    $.ajax({
        data: {tid: $(el).data('tid'), token: $(el).data('token')},
        dataType: 'JSON', type: 'POST', url: '/forum/bookmark/perform',
        success: function(data) {

            if (data.status == 'error'){
                notify('error', data.message);
                return false;
            }

            if (data.status == 'added'){
                notify('success', data.message);
                $(el).text('Из закладок');
            }

            if (data.status == 'deleted'){
                notify('success', data.message);
                $(el).text('В закладки');
            }
        }
    });
    return false;
}

/* Удаление сообщения в форуме */
function deletePost(el) {

    $.ajax({
        data: {tid: $(el).data('tid'), token: $(el).data('token')},
        dataType: 'JSON', type: 'POST', url: '/forum/active/delete',
        success: function(data) {

            if (data.status == 'error'){
                notify('error', data.message);
                return false;
            }

            if (data.status == 'success'){
                notify('success', 'Сообщение успешно удалено');

                $(el).closest('.post').hide('slow');
            }
        }
    });

    return false;
}

/* Изменение рейтинга */
function changeRating(el) {

    $.ajax({
        data: {
            id: $(el).data('id'),
            type: $(el).data('type'),
            vote: $(el).data('vote'),
            token: $(el).data('token')
        },
        dataType: 'JSON', type: 'POST', url: '/ajax/rating',
        success: function(data) {

            if (data.status == 'error'){
                return false;
            }

            if (data.status == 'success'){
                rating = $(el).closest('.js-rating').find('span');

                $(el).closest('.js-rating').find('a').removeClass('active');

                if (! data.cancel) {
                    $(el).addClass('active');
                }

                rating.text(data.count);
            }
        }
    });
    return false;
}
