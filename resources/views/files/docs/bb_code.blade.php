@section('title')
    Функция bb_code
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/files/docs">Документация Rotor</a></li>
            <li class="breadcrumb-item active">Функция bb_code</li>
        </ol>
    </nav>
@stop

Функция преобразовывает bb-коды в html-теги, между открывающим и закрывающим тегов могу быть переводы строк, неправильная вложенность тегов игнорируется<br>
Данная функция использует другие функции: <a href="/files/docs/hidden_text">hidden_text</a>, <a href="/files/docs/url_replace">url_replace</a>, <a href="/files/docs/highlight_code">highlight_code</a><br><br>

<pre class="d">
<b>bb_code</b>(
    string msg
);
</pre><br>

<b>Параметры функции</b><br>

<b>msg</b> - Текст в котором необходимо преобразовать все bb-коды в html-теги, список доступных тегов (code, hide, url, big, b, i, u, small, red, green, blue, q, del)<br><br>

<b>Примеры использования</b><br>

<?php
echo bbCode(check('[code]<?php
echo bbCode(\'Текст сообщения [url=https://visavi.net]Visavi[/url]\');
/* Результат выполнения функции представлен ниже */
?>[/code]'));

echo bbCode('Текст сообщения [url=https://visavi.net]Visavi[/url]').'<br>';
?>
