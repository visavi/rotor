@section('title')
    Функция check
@stop

<h1>Функция check</h1>

<nav>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
        <li class="breadcrumb-item"><a href="/files/docs">Документация Rotor</a></li>
        <li class="breadcrumb-item active">Функция check</li>
    </ol>
</nav>

Фильтрует данные путём замены служебных символов и тегов на html сущности. Используется для обработки GET и POST запросов,  также применяется для фильтрация массивов.<br>
Данные дополнительно обрабатывается функцией <a href="http://ru2.php.net/manual/ru/function.stripslashes.php">stripslashes</a>  (Удаление экранирования символов) и <a href="http://ru2.php.net/manual/ru/function.trim.php">trim</a> (Удаление пробелов в начале и конце строки)<br><br>

<pre class="d">
<b>check</b>(
    mixed msg
);
</pre><br>

<b>Параметры функции</b><br>
<b>msg</b> - Входная строка или одномерный массив<br><br>

<b>Примеры использования</b><br>

<?php
echo bbCode(check('[code]<?php
//Фильтрация строки
echo check(\'<b>Hello world</b>\'); /* &lt;b&gt;Hello world&lt;/b&gt; */

//Фильтрация массива
var_dump(check(array(0=>\'$500\', \'name\'=>\'Alex%and\er\')));
/*
array(2) {
    [0]=>
    string(8) "&#36;500"
    ["name"]=>
    string(19) "Alex&#37;and&#92;er"
}
*/
?>[/code]'));
?>
