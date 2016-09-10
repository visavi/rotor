$(document).ready(function(){

    prettyPrint();

    bootbox.setDefaults({ locale: 'ru' });

    toastr.options = {
        "progressBar": true,
        "positionClass": "toast-top-full-width",
    };

    $('#markItUp').markItUp(mySettings);
    $('#markItUpHtml').markItUp(myHtmlSettings);

    $('[data-toggle="tooltip"]').tooltip();
    $('[data-toggle="popover"]').popover()

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
});

/* Вывод уведомлений */
function notify(type, title, message, optionsOverride) {
    return toastr[type](message, title, optionsOverride);
}

/* Показ формы загрузки файла */
function showAttachForm(){
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
function postReply(name){

    postJump();
    separator = $("#markItUp").val().length ? '\n' : '';
    $('#markItUp').focus().val($('#markItUp').val() + separator + '[b]' + name + '[/b], ');

    return false;
}

/* Цитирование сообщения */
function postQuote(el){

    postJump();

    var post = $(el).closest('.post');
    var author = post.find('b').text();
    var date = post.find('small').text();
    var message = post.find('.message').text();

    separator = $("#markItUp").val().length ? '\n' : '';
    $('#markItUp').focus().val($('#markItUp').val() + separator + '[quote=' + author + ' ' + date + ']' + message + '[/quote]\n');

    return false;
}

/* Отправка жалобы на спам */
function sendComplaint(el) {
    bootbox.confirm('Вы действительно хотите отправить жалобу?', function(result){
        if (result) {

            $.ajax({
                data: {id: $(el).data('id'), type: $(el).data('type'), token: $(el).data('token')},
                dataType: 'JSON', type: 'POST', url: $(el).data('type') + '/complaint',
                success: function(data) {

                    $(el).replaceWith('<span class="fa fa-bell-slash-o"></span>');

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
