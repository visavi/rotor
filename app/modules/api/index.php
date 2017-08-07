<?php
App::view(Setting::get('themes').'/index');

//show_title('API интерфейс');
?>

<i class="fa fa-cog"></i> <b><a href="/api">api</a></b> - Главная страница с описанием интерфейса<br />
<i class="fa fa-cog"></i> <b><a href="/api/user">api/user</a></b> - Параметры: key, Возвращаемые значения: массив данных из профиля пользователя<br />
<i class="fa fa-cog"></i> <b><a href="/api/private">api/private</a></b> - Параметры: key, count = 10, Возвращаемые значения: total - кол. сообщений, messages - массив приватных сообщений<br />
<i class="fa fa-cog"></i> <b><a href="/api/forum">api/forum</a></b> - Параметры: key, id, Возвращаемые значения: id - id темы, author - автор темы, title - заголовок темы, messages - массив постов<br />

<br />Для доступа к данным нужен API-ключ, которые можно получить на странице мои данные<br /><br />

Пример использования
<pre class="prettyprint linenums">
 /api/user?key=Ключ
</pre>

Возвращает json
<pre class="prettyprint linenums">
{"login":"admin","email":"vantuzilla@yandex.ru","name":"Александр","country":"","city":"","site":"http:\/\/visavi.net","icq":"","skype":"","gender":"1","birthday":"","newwall":"0","point":"540","money":"110675","ban":"0","allprivat":"1","newprivat":"0","status":"<span style=\"color:#ff0000\">\u0410\u0434\u043c\u0438\u043d\u0438\u0441\u0442\u0440\u0430\u0442\u043e\u0440<\/span>","avatar":"","picture":"","rating":"0","lastlogin":"1477663978"}
</pre>

<?php
App::view(Setting::get('themes').'/foot');
