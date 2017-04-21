<?php
if (isset($act) && $act == 'recent') {
	//show_title('Последняя активность');
}

echo '<div class="b"><i class="fa fa-forumbee fa-lg text-muted"></i> <b>Последние темы</b></div>';
recenttopics();

echo '<div class="b"><i class="fa fa-download fa-lg text-muted"></i> <b>Последние файлы</b></div>';
recentfiles();

echo '<div class="b"><i class="fa fa-globe fa-lg text-muted"></i> <b>Последние статьи</b></div>';
recentblogs();

echo '<div class="b"><i class="fa fa-hashtag fa-lg text-muted"></i>  <b>Последние cобытия</b></div>';
recentevents();

echo '<div class="b"><i class="fa fa-image fa-lg text-muted"></i> <b>Последние фотографии</b></div>';
recentphotos();
