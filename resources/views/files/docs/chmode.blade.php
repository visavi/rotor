@section('title', 'Функция chmode')

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/files/docs">Документация Rotor</a></li>
            <li class="breadcrumb-item active">Функция chmode</li>
        </ol>
    </nav>
@stop

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
chmode(storage_path(\'forum\'));
?>[/code]'));
?>
