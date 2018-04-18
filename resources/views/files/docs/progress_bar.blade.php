@section('title')
    Функция progress_bar
@stop

<h1>Функция progress_bar</h1>

<nav>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
        <li class="breadcrumb-item"><a href="/files/docs">Документация Rotor</a></li>
        <li class="breadcrumb-item active">Функция progress_bar</li>
    </ol>
</nav>

Функция выводит прогресс-бар, на основе введенных данных (Доступно с версии 3.0.2)<br><br>

<pre class="d">
<b>progress_bar</b>(
    int percent,
    string title = ''
);
</pre><br>

<b>Параметры функции</b><br>

<b>percent</b> - Количество процентов в графике (от 0 до 100)<br>
<b>title</b> - Альтернативное название (По умолчанию пусто), если данные не переданы, то название берется из параметра percent<br><br>

<b>Примеры использования</b><br>

<?php
echo bbCode(check('[code]<?php
progressBar(55);
progressBar(35, \'Батарейка\');
/* Результат выполнения функции представлен ниже */
?>[/code]'));

progressBar(55).'<br>';
progressBar(35, 'Батарейка').'<br>';
?>
