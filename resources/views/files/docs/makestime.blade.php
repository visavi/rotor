@section('title', 'Функция makestime')

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/files/docs">Документация Rotor</a></li>
            <li class="breadcrumb-item active">Функция makestime</li>
        </ol>
    </nav>
@stop

Функция переводит количество секунд в удобный формат времени, для вывода времени до 1 дня можно использовать функцию <a href="/files/docs/maketime">maketime</a><br><br>

<pre class="d">
<b>makestime</b>(
    int time
);
</pre><br>

<b>Параметры функции</b><br>

<b>time</b> - секунды<br><br>

<b>Примеры использования</b><br>

<?php
echo bbCode(check('[code]<?php
echo makestime (300); /* 0 дн. 00:05:00 */
echo makestime (86400); /* 1 дн. 00:00:00 */
?>[/code]'));
?>
