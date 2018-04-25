@section('title')
    Функция profile
@stop

<h1>Функция profile</h1>

<nav>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
        <li class="breadcrumb-item"><a href="/files/docs">Документация Rotor</a></li>
        <li class="breadcrumb-item active">Функция profile</li>
    </ol>
</nav>

Выводит ссылку на профиль пользователя (Доступно с версии 3.0.0)<br><br>

<pre class="d">
<b>profile</b>(
    string login,
    string color = &#39;&#39;
);
</pre><br>

<b>Параметры функции</b><br>

<b>login</b> - Логин пользователя<br>
<b>color</b> - Цвет логина, по умолчанию цвет текста не устанавливается, для вывода необходимо установить код цвета в шестнадцатеричном формате, пример #ff0000 или в виде ключевого слова например black, red<br><br>


<b>Примеры использования</b><br>

<?php
echo bbCode(check('[code]<?
profile(\'Username\'); /* <a href="/user/Username">Username</a>  */
profile(\'Username\', \'#ff0000\'); /* <a href="/users/Username"><span style="color:#ff0000">Username</span></a> */
?>[/code]'));
?>
