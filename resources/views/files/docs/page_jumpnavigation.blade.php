@section('title')
    Функция page_jumpnavigation
@stop

<h1>Функция page_jumpnavigation</h1>

<nav>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
        <li class="breadcrumb-item"><a href="/files/docs">Документация Rotor</a></li>
        <li class="breadcrumb-item active">Функция page_jumpnavigation</li>
    </ol>
</nav>

Функция постраничной навигации, выводит ссылки Назад и Далее (<span style="color:#ff0000">Удалено с версии 3.0.0</span>)<br>
Данная функция заменена на более расширенную <a href="/files/docs/page_strnavigation">page_strnavigation</a>, которая выводит эти ссылки а также номера страниц<br><br>

<pre class="d">
<b>page_jumpnavigation</b>(
    string link,
    int posts,
    int start,
    int total
);
</pre><br>

<b>Параметры функции</b><br>

<b>string</b> - Ссылка на страницу<br>
<b>posts</b> - Количество сообщений на страницу<br>
<b>start</b> - Текущая страница<br>
<b>total</b> - Количество всех сообщений<br><br>

<div class="info"><b>Примечание</b><br>
Данная функция является устаревшей, не рекомендуется ее использовать в своих проектах (<span style="color:#ff0000">Удалено с версии 3.0.0</span>)
</div><br>

<b>Примеры использования</b><br>

<?php
echo bbCode(check('[code]<?php
page_jumpnavigation(\'/chat?\', 10, $start, $total); /* <-Назад | Далее-> */
?>[/code]'));
?>
