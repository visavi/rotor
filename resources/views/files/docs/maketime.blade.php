@section('title')
    Функция maketime
@stop

<h1>Функция maketime</h1>

<nav>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
        <li class="breadcrumb-item"><a href="/files/docs">Документация Rotor</a></li>
        <li class="breadcrumb-item active">Функция maketime</li>
    </ol>
</nav>

Функция переводит количество секунд в удобный формат времени, при количестве секунд более чем 86400 (Сутки), рекомендуется использовать функцию <a href="/files/docs/makestime">makestime</a><br><br>

<pre class="d">
<b>maketime</b>(
    int time
);
</pre><br>

<b>Параметры функции</b><br>

<b>time</b> - секунды (0-86400), если секунд более чем 86400, то функция игнорирует это время<br><br>

<b>Примеры использования</b><br>

<?php
echo bbCode(check('[code]<?php
echo maketime (300); /* 05:00 */
echo maketime (3600); /* 01:00:00 */
echo maketime (86400); /* 00:00:00 */
?>[/code]'));
?>
