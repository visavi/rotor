@section('title')
    Функция cache_functions
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/files/docs">Документация Rotor</a></li>
            <li class="breadcrumb-item active">Функция cache_functions</li>
        </ol>
    </nav>
@stop

Функция кэширует и сохраняет в файл список всех пользовательских функций, которые лежать в директории includes/functions, по умолчанию кэширует на 3 часа
<br>
Это системная функция вызывается автоматически в файле includes/functions.php
<br><br>

<pre class="d">
<b>cache_functions</b>(
    int cache
);
</pre><br>

<b>Параметры функции</b><br>

<b>cache</b> - Период в секундах через который будет происходить проверка и автоматическое обновление списка функций
<br><br>

<b>Примеры использования</b><br>
<?php
echo bbCode(check('[code]<?php
$functions = cache_functions();
?>[/code]'));
?>
