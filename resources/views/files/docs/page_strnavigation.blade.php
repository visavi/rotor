<?php //show_title('Функция page_strnavigation'); ?>

Функция постраничной навигации, выводит страницы, а также ссылки Назад и Далее<br>
Также показывает всегда первую и последнюю страницу, разрывы выводятся в виде многоточия ...<br><br>

<pre class="d">
<b>page_strnavigation</b>(
	string link,
	int posts,
	int start,
	int total,
	int range = 3
);
</pre><br>

<b>Параметры функции</b><br>

<b>string</b> - Ссылка на страницу<br>
<b>posts</b> - Количество сообщений на страницу<br>
<b>start</b> - Текущая страница<br>
<b>total</b> - Количество всех сообщений<br>
<b>range</b> - Количество выводимых страниц слева и справа от текущей<br><br>

<b>Примеры использования</b><br>

<?php
echo bbCode(check('[code]<?php
page_strnavigation(\'/chat?\', 10, $start, $total); /* Страницы: « 1 ... 5 6 7 [8] 9 10 11 ... 50 » */
?>[/code]'));
?>

<br>
<i class="fa fa-arrow-circle-left"></i> <a href="/files/docs">Вернуться</a><br>
