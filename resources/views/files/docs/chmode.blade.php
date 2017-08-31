<?php //show_title('Функция chmode'); ?>

Функция автоустановки прав доступа<br>
Сканирует папку и выставляет на файлы доступ - 0777<br>

<pre class="d">
<b>chmode</b>(
	string path
);
</pre><br>

<b>Параметры функции</b><br>

<b>path</b> - Директория в которой изменяются права файлов.<br><br>

<b>Примеры использования</b><br>
<?php
echo bbCode(check('[code]<?php
chmode(STORAGE.\'/forum\');
?>[/code]'));
?>

<br>
<i class="fa fa-arrow-circle-left"></i> <a href="/files/docs">Вернуться</a><br>
