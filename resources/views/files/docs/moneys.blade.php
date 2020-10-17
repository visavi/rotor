@section('title', 'Функция moneys')

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/files/docs">Документация Rotor</a></li>
            <li class="breadcrumb-item active">Функция moneys</li>
        </ol>
    </nav>
@stop

Преобразует число в сумму денег с окончанием в виде названия валюты, окончания устанавливаются в настройках админки через запятую, по умолчанию (рублей, рубля, рубль)<br>
Можно указать только один вариант окончания, тогда оно будет подставляться для всех чисел передаваемых в функцию<br><br>

<pre class="d">
<b>moneys</b>(
    int sum
);
</pre><br>

<b>Параметры функции</b><br>

<b>sum</b> - Количество денег<br><br>


<b>Примеры использования</b><br>

<?php
echo bbCode(check('[code]<?php
echo moneys(1); /* 1 рубль */
echo moneys(2); /* 2 рубля */
echo moneys(5); /* 5 рублей */
?>[/code]'));
?>
