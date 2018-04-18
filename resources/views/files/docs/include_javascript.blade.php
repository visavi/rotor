@section('title')
    Функция include_javascript
@stop

<h1>Функция include_javascript</h1>

<nav>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
        <li class="breadcrumb-item"><a href="/files/docs">Документация Rotor</a></li>
        <li class="breadcrumb-item active">Функция include_javascript</li>
    </ol>
</nav>

Функция проверяет, включена ли у пользователя в настройках опция вывода Javascript, если да, то подключается библиотека Jquery и некоторые вспомогательные jquery-плагины
<br>
Функция прописана в шаблонах тем
<br><br>

<pre class="d">
<b>includeScript();</b>
</pre><br>

<b>Параметры функции</b><br>

Функция не имеет параметров
<br><br>

<b>Примеры использования</b><br>
<?php
echo bbCode(check('[code]<?php
includeScript();
?>[/code]'));
?>
