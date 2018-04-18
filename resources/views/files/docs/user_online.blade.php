@section('title')
    Функция user_online
@stop

<h1>Функция user_online</h1>

<nav>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
        <li class="breadcrumb-item"><a href="/files/docs">Документация Rotor</a></li>
        <li class="breadcrumb-item active">Функция user_online</li>
    </ol>
</nav>

Определяет по указанному логину его статус на сайте. Online или Offline. По умолчанию данные кэшируются на 10 секунд<br>

<pre class="d">
<b>user_online</b>(
    string login
);
</pre><br>

<b>Параметры функции</b><br>

<b>login</b> - Логин пользователя<br><br>

<b>Примеры использования</b><br>
<?php
echo bbCode(check('[code]<?php
echo userOnline(\'Vantuz\'); /* [On] или [Off] */
?>[/code]'));
?>
