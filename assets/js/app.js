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
