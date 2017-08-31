<?php
view(setting('themes').'/index');

if (setting('closedsite') == 2) {
    echo '<center><br><br><h2>Внимание! Сайт закрыт по техническим причинам</h2></center>';

    echo 'Администрация сайта приносит вам свои извинения за возможные неудобства.<br>';
    echo 'Работа сайта возможно возобновится в ближайшее время.<br><br>';
} else {
    redirect('/');
}

view(setting('themes').'/foot');
