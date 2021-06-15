@section('title', 'Функция addmail')

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/files/docs">Документация Rotor</a></li>
            <li class="breadcrumb-item active">Функция addmail</li>
        </ol>
    </nav>
@stop

Функция для отправки писем на email, отсылает письма через стандартную функцию <a href="http://ru.php.net/manual/ru/function.mail.php">mail()</a>, данные отправляются в кодировке UTF-8<br><br>

<pre class="prettyprint linenums">
<b>addmail</b>(
    string mail,
    string subject,
    string messages,
    string sendermail = "",
    string sendername = ""
);
</pre><br>

<b>Параметры функции</b><br>

<b>mail</b> - email на который отсылаем сообщение<br>
<b>subject</b> - Тема сообщения<br>
<b>messages</b> - Текст сообщения<br>
<b>sendermail</b> - email отправителя, если оставить пустым, то письмо будет отправлено c электронной почты администратора<br>
<b>sendername</b> - Имя отправителя, если оставить пустым, то письмо будет отправлено от имени администратора сайта<br><br>

<b>Примеры использования</b><br>
<?php
echo bbCode(check('[code]<?php
echo addmail(\'nobody@example.com\', \'Это тема\', \'Это текст сообщения\'); /* Отправит письмо от администратора сайта */
echo addmail(\'nobody@example.com\', \'Это тема\', \'Это текст сообщения\', \'webmaster@example.com\', \'webmaster\'); /* Отправит письмо от пользователя webmaster */
?>[/code]'));
?>
