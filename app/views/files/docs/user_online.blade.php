<?php //show_title('Функция user_online'); ?>

Определяет по указанному логину его статус на сайте. Online или Offline. По умолчанию данные кэшируются на 10 секунд<br />

<pre class="d">
<b>user_online</b>(
	string login
);
</pre><br />

<b>Параметры функции</b><br />

<b>login</b> - Логин пользователя<br /><br />

<b>Примеры использования</b><br />
<?php
echo App::bbCode(check('[code]<?php
echo user_online(\'Vantuz\'); /* [On] или [Off] */
?>[/code]'));
?>

<br />
<i class="fa fa-arrow-circle-left"></i> <a href="/files/docs">Вернуться</a><br />
