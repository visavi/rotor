@section('title')
    Функция redirect
@stop

<h1>Функция redirect</h1>

<nav>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
        <li class="breadcrumb-item"><a href="/files/docs">Документация Rotor</a></li>
        <li class="breadcrumb-item active">Функция redirect</li>
    </ol>
</nav>

Перенаправляет пользователя на другую страницу, при обработке функции автоматически вызывается функция exit (Доступно с версии 2.6.5)<br><br>

<pre class="d">
<b>redirect</b>(
    string url,
    boolean permanent = false
);
</pre><br>

<b>Параметры функции</b><br>

<b>url</b> - Ссылка на страницу, может быть указан как абсолютный так и относительный путь<br>
<b>permanent</b> - Устанавливает постоянное перенаправление, если передать true, то перед перенаправлением будет вызван заголовок header('HTTP/1.1 301 Moved Permanently');<br><br>


<b>Примеры использования</b><br>

<?php
echo bbCode(check('[code]<?php
redirect("/"); /* Перенаправляет на главную страницу */
redirect("/", true); /* Устанавливает постоянное перенаправление на главную страницу */
?>[/code]'));
?>
