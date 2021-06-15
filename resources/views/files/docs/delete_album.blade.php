@section('title', 'Функция delete_album')

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/files/docs">Документация Rotor</a></li>
            <li class="breadcrumb-item active">Функция delete_album</li>
        </ol>
    </nav>
@stop

Полностью удаляет фотоальбом пользователя, удаляются все фотографии в галерее, а также аватар и персональное фото в анктете, при этом сам профиль пользователя и остальные данные не затрагиваются<br>
Является вспомогательной для функции <a href="/files/docs/delete_users">delete_users</a>
<br><br>

<pre class="prettyprint linenums">
<b>delete_album</b>(
    string user
);
</pre><br>

<b>Параметры функции</b><br>

<b>user</b> - Логин пользователя<br><br>

<b>Примеры использования</b><br>

<?php
echo bbCode(check('[code]<?php
deleteAlbum("Vantuz");
/* Полностью удаляет фотоальбом пользователя Vantuz, а также аватар и персональную фотографию в анкете */
?>[/code]'));
?>
