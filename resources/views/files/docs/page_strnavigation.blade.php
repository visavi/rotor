@section('title')
    Функция page_strnavigation
@stop

<h1>Функция page_strnavigation</h1>

<nav>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
        <li class="breadcrumb-item"><a href="/files/docs">Документация Rotor</a></li>
        <li class="breadcrumb-item active">Функция page_strnavigation</li>
    </ol>
</nav>

Функция постраничной навигации, выводит страницы, а также ссылки Назад и Далее<br>
Также показывает всегда первую и последнюю страницу, разрывы выводятся в виде многоточия ...<br><br>

<pre class="d">
<b>page_strnavigation</b>(
    string link,
    int posts,
    int start,
    int total,
    int range = 3
);
</pre><br>

<b>Параметры функции</b><br>

<b>string</b> - Ссылка на страницу<br>
<b>posts</b> - Количество сообщений на страницу<br>
<b>start</b> - Текущая страница<br>
<b>total</b> - Количество всех сообщений<br>
<b>range</b> - Количество выводимых страниц слева и справа от текущей<br><br>

<b>Примеры использования</b><br>

<?php
echo bbCode(check('[code]<?php
page_strnavigation(\'/chat?\', 10, $start, $total); /* Страницы: « 1 ... 5 6 7 [8] 9 10 11 ... 50 » */
?>[/code]'));
?>
