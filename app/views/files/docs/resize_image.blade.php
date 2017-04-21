<?php //show_title('Функция resize_image'); ?>

Функция ресайза и кэширования уменьшенных копий картинок, проверяет сделана ли миниатюра изображения, если нет, то автоматически уменьшает картинку и сохраняет ее в специальной папке uploads/thumbnail.<br />
Если размер оригинального изображения менее чем требуемый размер уменьшенной копии, то обработка не производится<br />
Если отсутствует исходное изображение, то выводится стандартная картинка с уведомлением об ошибке (Доступно с версии 2.4.1)<br /><br />

<pre class="d">
<b>resize_image</b>(
	string dir,
	string name,
	int size,
	array params = []
);
</pre><br />

<b>Параметры функции</b><br />

<b>dir</b> - Директория с оригинальным изображением<br />
<b>name</b> - Имя файла оригинального изображения<br />
<b>size</b> - Размер уменьшения картинки, можно взять из настроек в админке App::setting('previewsize')<br />
<b>params</b> - Массив параметров<br /><br />


<b>Примеры использования</b><br />

<?php
echo App::bbCode(check('[code]<?php
echo resize_image(\'/uploads/pictures/\', $data[\'link\'], $config[\'previewsize\'], [\'alt\' = $data[\'title\']]);
 /* Функция вернет уменьшенную картинку <img src="/uploads/thumbnail/upload_pictures_1350.jpg" alt="Название"/> */
?>[/code]'));
?>

<br />
<i class="fa fa-arrow-circle-left"></i> <a href="/files/docs">Вернуться</a><br />
