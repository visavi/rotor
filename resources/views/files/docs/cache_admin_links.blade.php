@section('title', 'Функция cache_admin_links')

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/files/docs">Документация Rotor</a></li>
            <li class="breadcrumb-item active">Функция cache_admin_links</li>
        </ol>
    </nav>
@stop

Функция кэширует ссылки на дополнительные админские страницы<br>
Является вспомогательной для функции вывода ссылок <a href="/files/docs/show_admin_links">show_admin_links</a><br>
По умолчанию кэширует на 3 часа
<br><br>

<pre class="d">
<b>cache_admin_links</b>(
  int cache
);
</pre><br>

<b>Параметры функции</b><br>

<b>cache</b> - Период в секундах через который будет происходить проверка и автоматическое обновление админских ссылок
<br><br>

<b>Примеры использования</b><br>
<?php
echo bbCode(check('[code]<?php
$links = cacheAdminLinks();
// Вызывается внутри функции showAdminLinks()
?>[/code]'));
?>
