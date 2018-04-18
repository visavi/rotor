@section('title')
    Функция unlink_image
@stop

<h1>Функция unlink_image</h1>

<nav>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
        <li class="breadcrumb-item"><a href="/files/docs">Документация Rotor</a></li>
        <li class="breadcrumb-item active">Функция unlink_image</li>
    </ol>
</nav>

Удаляет картинку, а также кэшированное изображение сохраненное в директории uploads/thumbnail, с проверкой имеется ли картинка в данных директориях (Доступно с версии 2.6.0)<br><br>

<pre class="d">
<b>unlink_image</b>(
    string dir,
    string image
);
</pre><br>

<b>Параметры функции</b><br>

<b>dir</b> - Директория с изображением<br>
<b>image</b> - Имя файла с изображением<br><br>


<b>Примеры использования</b><br>

<?php
echo bbCode(check('[code]<?php
deleteImage("uploads/photos/", "Vantuz.gif");
/* Удаляет файл Vantuz.gif сначала в директории uploads/photos, а затем в uploads/thumbnail */
?>[/code]'));
?>
