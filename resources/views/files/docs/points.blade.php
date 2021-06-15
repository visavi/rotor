@section('title', 'Функция points')

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/files/docs">Документация Rotor</a></li>
            <li class="breadcrumb-item active">Функция points</li>
        </ol>
    </nav>
@stop

Функция правильного окончания актива сайта, окончания устанавливаются в настройках админки через запятую, по умолчанию (баллов, балла, балл)<br>
Можно указать только один вариант окончания, тогда оно будет подставляться для всех чисел передаваемых в функцию<br><br>

<pre class="prettyprint linenums">
<b>points</b>(
    int sum
);
</pre><br>

<b>Параметры функции</b><br>

<b>sum</b> - Количество баллов в целочисленном формате<br><br>


<b>Примеры использования</b><br>

<?php
echo bbCode(check('[code]<?php
echo points(1); /* 1 балл */
echo points(2); /* 2 балла */
echo points(5); /* 5 баллов */
?>[/code]'));
?>
