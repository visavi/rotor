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
});

/* Вывод спойлера */
$(document).ready(function(){
	$(".spoiler-body").hide();
	$(".spoiler-head").click(function(){
		$(this).toggleClass("open").toggleClass("closed").next().slideToggle();
	});
});

/* Показ новостей на главной */
$(document).ready(function(){
	$(".news-text").hide();
	$(".news-title").click(function () {
		$(this).nextAll("div.news-text:first").slideToggle();
 		//$(this).attr('src', '/images/img/ups.gif');
	});
});
