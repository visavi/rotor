@section('title')
    Class BBCode
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/files/docs">Документация Rotor</a></li>
            <li class="breadcrumb-item active">Class BBCode</li>
        </ol>
    </nav>
@stop

Пример
<pre class="prettyprint linenums">
$bbcode = new BBCode($config);

$bbcode->setParser('header', '/\[h1\](.*?)\[\/h1\]/s', '&lt;h1>$1&lt;/h1>');
$bbcode->except('bold')->only('header', 'italic');

$text = $bbcode->parse($text);
$text = $bbcode->parseStickers($text);
</pre>

Каждый парсер может состоять из нескольких параметров<br>

<b>pattern - шаблон регулярного выражения</b><br>
Пример: /\[b\](.*?)\[\/b\]/s<br><br>

<b>replace - шаблон замены</b><br>
Пример: &lt;strong>$1&lt;/strong><br><br>

<b>callback - поиск по регулярному выражению и замену с использованием callback-функции</b><br>
Необходимо указать имя функции для обработки, в этом случае параметр replace не учтется<br><br>

<b>iterate - количество итераций обработки</b><br>
Обрабатывает вложенности к примеру [b][b][b][b][b]Текст[/b][/b][/b][/b][/b], по умолчанию выполняется 1 прогон<br>
<br>

<b>Список доступных парсеров</b>
<ul>
    <li>code - исходный код</li>
    <li>bold - жирный текст</li>
    <li>italic - наклонный текст</li>
    <li>underLine- подчеркивание текста</li>
    <li>lineThrough - зачеркивание текста</li>
    <li>fontSize - размер текста</li>
    <li>fontColor - цвет текста</li>
    <li>center - центрирование текста</li>
    <li>quote - цитирование</li>
    <li>namedQuote - цитирование с параметром</li>
    <li>http - обычная ссылка</li>
    <li>link - ссылка</li>
    <li>namedLink - именованная ссылка</li>
    <li>image - картинка</li>
    <li>orderedList - сортированный список</li>
    <li>unorderedList - именованный список</li>
    <li>spoiler - спойлер</li>
    <li>shortSpoiler - именованный спойлер</li>
    <li>hide - скрытие текста</li>
    <li>youtube - видео</li>
</ul>



<h3>parse(string $source)</h3>
Обрабатывает текст с BB-кодами

<pre class="prettyprint linenums">
$text = $bbcode->parse($text);
</pre>

<h3>setParser(string $name, string $pattern, string $replace)</h3>
Добавляет новый парсер

<pre class="prettyprint linenums">
$bbcode->addParser('header', '/\[h1\](.*?)\[\/h1\]/s', '&lt;h1>$1&lt;/h1>');
</pre>
После этого станет доступна обработка текста [h1] текст [/h1]

<h3>only(mixed $only = null)</h3>
Устанавливает список используемых парсеров, можно передать массив или список через запятую

<pre class="prettyprint linenums">
$bbcode->only('bold', 'italic');
</pre>
будут обрабатываться только [b] и [i]

<h3>except(mixed $except = null)</h3>
Исключает парсеры из набора, можно передать массив или список через запятую

<pre class="prettyprint linenums">
$bbcode->except('bold', 'italic');
</pre>
После удаления [b]текст[/b] и [i]текст[/i] не будет обрабатываться


<h3>clear(string $source)</h3>
Очищает текст от BB-кодов
<pre class="prettyprint linenums">
$text = $bbcode->clear($text);
</pre>

<h3>getParsers()</h3>
Возвращает список всех парсеров
<pre class="prettyprint linenums">
var_dump($bbcode->getParsers());
</pre>

