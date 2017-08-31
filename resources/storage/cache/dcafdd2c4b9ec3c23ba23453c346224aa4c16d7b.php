<?php $__env->startSection('title'); ?>
    Справка по тегам - ##parent-placeholder-3c6de1b7dd91465d437ef415f94f36afc1fbc8a8##
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <h1>Справка по тегам</h1>

    Вы можете выражать свой текст следующими тегами:<br><br>

    <i class="fa fa-bold"></i> [b]<?php echo App::bbCode('[b]Жирный шрифт[/b]'); ?>[/b]<br>
    <i class="fa fa-italic"></i> [i]<?php echo App::bbCode('[i]Наклонный шрифт[/i]'); ?>[/i]<br>
    <i class="fa fa-underline"></i> [u]<?php echo App::bbCode('[u]Подчеркнутый шрифт[/u]'); ?>[/u]<br>
    <i class="fa fa-strikethrough"></i> [s]<?php echo App::bbCode('[s]Зачеркнутый шрифт[/s]'); ?>[/s]<br><br>

    <i class="fa fa-font"></i> Размер шрифтов от 1 до 5<br>
    <i class="fa fa-font"></i> [size=1]<?php echo App::bbCode('[size=1]Маленький шрифт[/size]'); ?>[/size]<br>
    <i class="fa fa-font"></i> [size=3]<?php echo App::bbCode('[size=3]Средний шрифт[/size]'); ?>[/size]<br>
    <i class="fa fa-font"></i> [size=5]<?php echo App::bbCode('[size=5]Большой шрифт[/size]'); ?>[/size]<br><br>

    <i class="fa fa-th"></i> Цвет текста в формате #ff0000<br>
    <i class="fa fa-th"></i> [color=#ff0000]<?php echo App::bbCode('[color=#ff0000]Красный шрифт[/color]'); ?>[/color]<br>
    <i class="fa fa-th"></i> [color=#00cc00]<?php echo App::bbCode('[color=#00cc00]Зеленый шрифт[/color]'); ?>[/color]<br>
    <i class="fa fa-th"></i> [color=#00ffff]<?php echo App::bbCode('[color=#00ffff]Голубой шрифт[/color]'); ?>[/color]<br><br>

    Для того чтобы вставить ссылку, можно просто написать http://адрес_cсылки<br>
    <i class="fa fa-link"></i> Для ссылки с названием: [url=http://адрес_cсылки] Название [/url]<br>
    <i class="fa fa-link"></i> Короткий способ: [url] http://адрес_cсылки [/url]<br><br>

    <i class="fa fa-image"></i> [img]Ссылка на изображение[/img]<br><?php echo App::bbCode('[img]'.Setting::get('home').'/assets/img/images/logo.png[/img]'); ?><br>
    <i class="fa fa-youtube-play"></i> [youtube]Код видео с youtube[/youtube]<br><?php echo App::bbCode('[youtube]yf_YWiqqv34[/youtube]'); ?><br>

    <i class="fa fa-align-center"></i> [center]Текст по центру[/center]<?php echo App::bbCode('[center]Текст по центру[/center]'); ?><br>
    <i class="fa fa-list-ul"></i> [list]Элементы списка[/list]<?php echo App::bbCode('[list]Элементы списка[/list]'); ?><br>
    <i class="fa fa-list-ol"></i> [list=1]Элементы нумерованного списка[/list]<?php echo App::bbCode('[list=1]Элементы списка[/list]'); ?><br>

    <i class="fa fa-text-height"></i> [spoiler]Выпадающий текст[/spoiler]<?php echo App::bbCode('[spoiler]Текст который показывается при нажатии[/spoiler]'); ?><br>
    <i class="fa fa-text-height"></i> [spoiler=Заголовок спойлера]Выпадающий текст[/spoiler]<?php echo App::bbCode('[spoiler=Заголовок спойлера]Текст который показывается при нажатии[/spoiler]'); ?><br>

    <i class="fa fa-eye-slash"></i> [hide]Скрытый текст[/hide]<?php echo App::bbCode('[hide]Для вставки скрытого текста[/hide]'); ?><br>
    <i class="fa fa-quote-right"></i> [quote]Цитата[/quote]<?php echo App::bbCode('[quote]Для вставки цитат[/quote]'); ?><br>
    <i class="fa fa-quote-right"></i> [quote=Автор цитаты]Цитата[/quote]<?php echo App::bbCode('[quote=Автор цитаты]Для вставки цитат[/quote]'); ?><br>

    <i class="fa fa-code"></i> [code]Форматированный код[/code]<?php echo App::bbCode('[code]&lt;? echo"Для вставки php-кода"; ?&gt;[/code]'); ?><br>
    <i class="fa fa-exchange"></i> [nextpage] - Служит для переноса текста на новую страницу (Работает только в блогах)<br>
    <i class="fa fa-scissors"></i> [cut] - Служит для обрезки текста (Работает только в новостях и событиях)<br><br>

    <i class="fa fa-eraser"></i> Очистка выделенного текста от bb-кода<br>
    <i class="fa fa-smile-o"></i> Вставка смайла из готового набора<br>
    <i class="fa fa-check-square-o"></i> Предварительный просмотр обработанного текста<br><br><br>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>