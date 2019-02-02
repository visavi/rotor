@section('title')
    Функция resize_image
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/files/docs">Документация Rotor</a></li>
            <li class="breadcrumb-item active">Функция resize_image</li>
        </ol>
    </nav>
@stop

Функция ресайза и кэширования уменьшенных копий картинок, проверяет сделана ли миниатюра изображения, если нет, то автоматически уменьшает картинку и сохраняет ее в специальной папке uploads/thumbnails.<br>
Если размер оригинального изображения менее чем требуемый размер уменьшенной копии, то обработка не производится<br>
Если отсутствует исходное изображение, то выводится стандартная картинка с уведомлением об ошибке (Доступно с версии 2.4.1)<br><br>

<pre class="d">
<b>resize_image</b>(
    string dir,
    string name,
    int size,
    array params = []
);
</pre><br>

<b>Параметры функции</b><br>

<b>dir</b> - Директория с оригинальным изображением<br>
<b>name</b> - Имя файла оригинального изображения<br>
<b>size</b> - Размер уменьшения картинки, можно взять из настроек в админке setting('previewsize')<br>
<b>params</b> - Массив параметров<br><br>


<b>Примеры использования</b><br>

<?php
echo bbCode(check('[code]<?php
echo resizeImage(\'/uploads/pictures/\', $data[\'link\'], [\'alt\' = $data[\'title\']]);
 /* Функция вернет уменьшенную картинку <img src="/uploads/thumbnails/upload_pictures_1350.jpg" alt="Название"/> */
?>[/code]'));
?>
