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
	$(".news-text").hide();
	$(".news-title").click(function () {
		$(this).nextAll(".news-text:first").slideToggle();
 		//$(this).attr('src', '/images/img/ups.gif');
	});
});

function showAttachForm(){
	$('.js-attach-button').hide();
	$('.js-attach-form').slideDown();
}
