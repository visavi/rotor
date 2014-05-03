<?php
#---------------------------------------------#
#      ********* RotorCMS *********           #
#           Author  :  Vantuz                 #
#            Email  :  visavi.net@mail.ru     #
#             Site  :  http://visavi.net      #
#              ICQ  :  36-44-66               #
#            Skype  :  vantuzilla             #
#---------------------------------------------#
require_once ('../includes/start.php');
require_once ('../includes/functions.php');
require_once ('../includes/header.php');
include_once ('../themes/header.php');

show_title('Справка по тегам');

echo 'Вы можете выражать свой текст следующими тегами:<br /><br />';

echo '<img src="/assets/markitup/images/bold.png" alt="bold" /> [b]'.bb_code('[b]Жирный шрифт[/b]').'[/b]<br />';
echo '<img src="/assets/markitup/images/superscript.png" alt="superscript" /> [big]'.bb_code('[big]Большой шрифт[/big]').'[/big]<br />';
echo '<img src="/assets/markitup/images/subscript.png" alt="subscript" /> [small]'.bb_code('[small]Маленький шрифт[/small]').'[/small]<br />';
echo '<img src="/assets/markitup/images/italic.png" alt="italic" /> [i]'.bb_code('[i]Наклонный шрифт[/i]').'[/i]<br />';
echo '<img src="/assets/markitup/images/underline.png" alt="underline" /> [u]'.bb_code('[u]Подчеркнутый шрифт[/u]').'[/u]<br />';

echo '<img src="/assets/markitup/images/strike.png" alt="strike" /> [del]'.bb_code('[del]Зачеркнутый шрифт[/del]').'[/del]<br />';
echo '<img src="/assets/markitup/images/colors.png" alt="red" /> [red]'.bb_code('[red]Красный шрифт[/red]').'[/red]<br />';
echo '<img src="/assets/markitup/images/colors.png" alt="green" /> [green]'.bb_code('[green]Зеленый шрифт[/green]').'[/green]<br />';
echo '<img src="/assets/markitup/images/colors.png" alt="blue" /> [blue]'.bb_code('[blue]Голубой шрифт[/blue]').'[/blue]<br />';

echo '<img src="/assets/markitup/images/youtube.png" alt="youtube" /> [youtube]Код видео с youtube[/youtube]<br />'.bb_code('[youtube]yf_YWiqqv34[/youtube]').'<br />';

echo '<img src="/assets/markitup/images/spoiler.png" alt="spoiler" /> [spoiler]Выпадающий текст[/spoiler]'.bb_code('[spoiler]Текст который показывается при нажатии[/spoiler]').'<br />';
echo '<img src="/assets/markitup/images/hidden.png" alt="hidden" /> [hide]Скрытый текст[/hide]'.bb_code('[hide]Для вставки скрытого текста[/hide]').'<br />';
echo '<img src="/assets/markitup/images/quotes.png" alt="quotes" /> [q]Цитата[/q]'.bb_code('[q]Для вставки цитат[/q]').'<br />';
echo '<img src="/assets/markitup/images/code.png" alt="code" /> [code]Форматированный код[/code]'.bb_code('[code]&lt;? echo"Для вставки php-кода"; ?&gt;[/code]').'<br />';
echo '<img src="/assets/markitup/images/next.png" alt="next" /> [nextpage] - Служит для переноса текста на новую страницу (Работает только в блогах)<br />';
echo '<img src="/assets/markitup/images/cut.png" alt="next" /> [cut] - Служит для обрезки текста (Работает только в новостях и событиях)<br /><br />';

echo 'Для того чтобы вставить ссылку, можно просто написать http://адрес_cсылки<br />';
echo '<img src="/assets/markitup/images/link.png" alt="link" /> Для ссылки с названием: [url=http://адрес_cсылки] Название [/url]<br />';
echo '<img src="/assets/markitup/images/clean.png" alt="clean" /> Очистка выделенного текста от bb-кода<br />';
echo '<img src="/assets/markitup/images/smile.png" alt="smile" /> Вставка смайла из готового набора<br /><br />';

include_once ('../themes/footer.php');
?>
