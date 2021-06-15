@section('title', 'Функция show_admin_links')

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/files/docs">Документация Rotor</a></li>
            <li class="breadcrumb-item active">Функция show_admin_links</li>
        </ol>
    </nav>
@stop

Функция выводит ссылки на дополнительные админские страницы <br>
Является основной для функции кэширования ссылок <a href="/files/docs/cache_admin_links">cache_admin_links</a>
<br><br>

<pre class="prettyprint linenums">
<b>show_admin_links</b>(
  int level = 0
);
</pre><br>

<b>Параметры функции</b><br>

<b>level</b> - Уровень доступа для которых нужно выводить данные ссылки. Доступные уровни: 101, 102, 103, 105. По умолчанию 0
<br><br>

<div class="info"><b>Примечание</b><br>
Если вызвать функцию без параметров, showAdminLinks(), то она будет выводить ссылки только для суперадмина, чей логин вписан в настройках сайта
</div><br>

<b>Примеры использования</b><br>
<?php
echo bbCode(check('[code]<?php
$links = cacheAdminLinks();
// Вызывается внутри функции showAdminLinks()
?>[/code]'));
?>
