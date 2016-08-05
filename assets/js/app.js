$(document).ready(function(){

	prettyPrint();

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

/* Цитирование сообщения  */
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
