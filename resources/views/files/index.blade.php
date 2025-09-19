@extends('layout')

@section('title', 'Собственные страницы сайта')

@section('header')
    <h1>Как создать свои страницы</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">Как создать свои страницы</li>
        </ol>
    </nav>
@stop

@section('content')
    1. Перейдите в директорию /resources/views/files, эта директория автоматически генерирует страницы сайта<br>
    2. Создайте в ней директорию с произвольным латинским названием (к примеру library)<br>
    3. Положите в созданную директорию обычный файл с расширением .blade.php (к примеру index.blade.php)<br>
    4. Напишите любой текст на этой странице, это может быть как html код, так и php<br>
    5. Теперь попробуйте перейти на созданную станицу, введите в браузере <?= config('app.url') ?>/files/library<br>
    6. Если страница отобразилась, значит вы все сделали правильно<br><br>

    <p class="alert alert-info">
        <i class="fa fa-exclamation-circle"></i> Все страницы сайта можно создавать, редактировать и удалять прямо из админки в разделе Редактирование страниц
    </p>

    <p class="text-muted fst-italic">
        В одной директории может быть неограниченное число файлов, расширение указывать не нужно, только имя папки и имя файла через слеш, к примеру /library/simplepage, /library/index то же что и просто /library <br><br>
        Также можно указать заголовок страницы, который автоматически подставится в блок title, для этого нужно написать следующий код
    </p>

<pre class="prettyprint linenums">
@@section('title', 'Новый заголовок страницы')
</pre><br>

    Значение в блоке title, будет подставлено в название страницы &lt;h1&gt;<br>
    Для того чтобы изменить блок название, то следует создать блок header<br>
<pre class="prettyprint linenums">
@@section('header')
    &lt;h1&gt;Измененное название страницы&lt;/h1&gt;
@@stop
</pre><br>

    Блок с навигацией также задается отдельно<br>

<pre class="prettyprint linenums">
@@section('breadcrumb')
    &lt;nav>
        &lt;ol class="breadcrumb">
            &lt;li class="breadcrumb-item">&lt;a href="/">&lt;i class="fas fa-home">&lt;/i>&lt;/a>&lt;li>
            &lt;li class="breadcrumb-item">&lt;a href="/files">Файлы&lt;/a>&lt;/li>
            &lt;li class="breadcrumb-item active">Активная страница&lt;/li>
        &lt;/ol>
    &lt;/nav>
@@stop
</pre><br>

    Дополнительно можно указать произвольные описание заполнив переменную setting('description')<br>

<pre class="prettyprint linenums">
@@section('description', 'Описание страницы')
</pre><br>

    Посмотрите пример страниц в виде <a href="/files/docs">документации Rotor</a><br>
@stop
