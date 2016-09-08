<?php
App::view($config['themes'].'/index');

show_title('Справка по тегам');

echo 'Вы можете выражать свой текст следующими тегами:<br /><br />';

echo '<i class="fa fa-bold"></i> [b]'.bb_code('[b]Жирный шрифт[/b]').'[/b]<br />';
echo '<i class="fa fa-italic"></i> [i]'.bb_code('[i]Наклонный шрифт[/i]').'[/i]<br />';
echo '<i class="fa fa-underline"></i> [u]'.bb_code('[u]Подчеркнутый шрифт[/u]').'[/u]<br />';
echo '<i class="fa fa-strikethrough"></i> [s]'.bb_code('[s]Зачеркнутый шрифт[/s]').'[/s]<br /><br />';

echo '<i class="fa fa-font"></i> Размер шрифтов от 1 до 5<br />';
echo '<i class="fa fa-font"></i> [size=1]'.bb_code('[size=1]Маленький шрифт[/size]').'[/size]<br />';
echo '<i class="fa fa-font"></i> [size=3]'.bb_code('[size=3]Средний шрифт[/size]').'[/size]<br />';
echo '<i class="fa fa-font"></i> [size=5]'.bb_code('[size=5]Большой шрифт[/size]').'[/size]<br /><br />';

echo '<i class="fa fa-th"></i> Цвет текста в формате #ff0000<br />';
echo '<i class="fa fa-th"></i> [color=#ff0000]'.bb_code('[color=#ff0000]Красный шрифт[/color]').'[/color]<br />';
echo '<i class="fa fa-th"></i> [color=#00cc00]'.bb_code('[color=#00cc00]Зеленый шрифт[/color]').'[/color]<br />';
echo '<i class="fa fa-th"></i> [color=#00ffff]'.bb_code('[color=#00ffff]Голубой шрифт[/color]').'[/color]<br /><br />';

echo 'Для того чтобы вставить ссылку, можно просто написать http://адрес_cсылки<br />';
echo '<i class="fa fa-link"></i> Для ссылки с названием: [url=http://адрес_cсылки] Название [/url]<br />';
echo '<i class="fa fa-link"></i> Короткий способ: [url] http://адрес_cсылки [/url]<br /><br />';

echo '<i class="fa fa-image"></i> [img]Ссылка на изображение[/img]<br />'.bb_code('[img]http://visavi.net/images/img/logo.png[/img]').'<br />';
echo '<i class="fa fa-youtube-play"></i> [youtube]Код видео с youtube[/youtube]<br />'.bb_code('[youtube]yf_YWiqqv34[/youtube]').'<br />';

echo '<i class="fa fa-align-center"></i> [center]Текст по центру[/center]'.bb_code('[center]Текст по центру[/center]').'<br />';
echo '<i class="fa fa-list-ul"></i> [list]Элементы списка[/list]'.bb_code('[list]Элементы списка[/list]').'<br />';
echo '<i class="fa fa-list-ol"></i> [list=1]Элементы нумерованного списка[/list]'.bb_code('[list=1]Элементы списка[/list]').'<br />';

echo '<i class="fa fa-text-height"></i> [spoiler]Выпадающий текст[/spoiler]'.bb_code('[spoiler]Текст который показывается при нажатии[/spoiler]').'<br />';
echo '<i class="fa fa-text-height"></i> [spoiler=Заголовок спойлера]Выпадающий текст[/spoiler]'.bb_code('[spoiler=Заголовок спойлера]Текст который показывается при нажатии[/spoiler]').'<br />';

echo '<i class="fa fa-eye-slash"></i> [hide]Скрытый текст[/hide]'.bb_code('[hide]Для вставки скрытого текста[/hide]').'<br />';
echo '<i class="fa fa-quote-right"></i> [quote]Цитата[/quote]'.bb_code('[quote]Для вставки цитат[/quote]').'<br />';
echo '<i class="fa fa-quote-right"></i> [quote=Автор цитаты]Цитата[/quote]'.bb_code('[quote=Автор цитаты]Для вставки цитат[/quote]').'<br />';

echo '<i class="fa fa-code"></i> [code]Форматированный код[/code]'.bb_code('[code]&lt;? echo"Для вставки php-кода"; ?&gt;[/code]').'<br />';
echo '<i class="fa fa-exchange"></i> [nextpage] - Служит для переноса текста на новую страницу (Работает только в блогах)<br />';
echo '<i class="fa fa-scissors"></i> [cut] - Служит для обрезки текста (Работает только в новостях и событиях)<br /><br />';

echo '<i class="fa fa-eraser"></i> Очистка выделенного текста от bb-кода<br />';
echo '<i class="fa fa-smile-o"></i> Вставка смайла из готового набора<br />';
echo '<i class="fa fa-check-square-o"></i> Предварительный просмотр обработанного текста<br /><br /><br />';

App::view($config['themes'].'/foot');
