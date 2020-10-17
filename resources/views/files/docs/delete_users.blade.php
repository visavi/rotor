@section('title', 'Функция delete_users')

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/files/docs">Документация Rotor</a></li>
            <li class="breadcrumb-item active">Функция delete_users</li>
        </ol>
    </nav>
@stop

Полностью удаляет пользователя с сайта, удаляются все записи в таблицах, а также некоторые загруженные файлы, к примеру аватар, персональное фото и фотографии в галерее<br>
При выполнении функции вызывается вспомогательная функция <a href="/files/docs/delete_album">delete_album</a>
<br><br>

<pre class="d">
<b>delete_users</b>(
    string user
);
</pre><br>

<b>Параметры функции</b><br>

<b>user</b> - Логин пользователя<br><br>

<b>Примеры использования</b><br>

<?php
echo bbCode(check('[code]<?php
deleteUser("Vantuz"); /* Полностью удаляет пользователя Vantuz */
?>[/code]'));
?>
