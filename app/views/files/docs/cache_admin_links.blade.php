<?php //show_title('Функция cache_admin_links'); ?>

Функция кэширует ссылки на дополнительные админские страницы<br />
Является вспомогательной для функции вывода ссылок <a href="/files/docs/show_admin_links">show_admin_links</a><br />
По умолчанию кэширует на 3 часа
<br /><br />

<pre class="d">
<b>cache_admin_links</b>(
  int cache
);
</pre><br />

<b>Параметры функции</b><br />

<b>cache</b> - Период в секундах через который будет происходить проверка и автоматическое обновление админских ссылок
<br /><br />

<b>Примеры использования</b><br />
<?php
echo App::bbCode(check('[code]<?php
$links = cache_admin_links();
// Вызывается внутри функции show_admin_links()
?>[/code]'));
?>

<br />
<i class="fa fa-arrow-circle-left"></i> <a href="/files/docs">Вернуться</a><br />
