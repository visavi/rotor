<?php //show_title('Функция delete_album'); ?>

Полностью удаляет фотоальбом пользователя, удаляются все фотографии в галерее, а также аватар и персональное фото в анктете, при этом сам профиль пользователя и остальные данные не затрагиваются<br>
Является вспомогательной для функции <a href="/files/docs/delete_users">delete_users</a>
<br><br>

<pre class="d">
<b>delete_album</b>(
	string user
);
</pre><br>

<b>Параметры функции</b><br>

<b>user</b> - Логин пользователя<br><br>

<b>Примеры использования</b><br>

<?php
echo App::bbCode(check('[code]<?php
delete_album("Vantuz");
/* Полностью удаляет фотоальбом пользователя Vantuz, а также аватар и персональную фотографию в анкете */
?>[/code]'));
?>

<br>
<i class="fa fa-arrow-circle-left"></i> <a href="/files/docs">Вернуться</a><br>
