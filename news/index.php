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

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';
$id = (isset($_GET['id'])) ? abs(intval($_GET['id'])) : 0;
$start = (isset($_GET['start'])) ? abs(intval($_GET['start'])) : 0;

show_title('Новости сайта');

switch ($act):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
case 'index':

	if (is_admin(array(101, 102))){
		echo '<div class="form"><a href="/admin/news.php">Управление новостями</a></div>';
	}

	$total = DB::run() -> querySingle("SELECT count(*) FROM `news`;");

	$page = floor(1 + $start / $config['postnews']);
	$config['description'] = 'Список новостей (Стр. '.$page.')';

	if ($total > 0) {
		if ($start >= $total) {
			$start = last_page($total, $config['postnews']);
		}

		$querynews = DB::run() -> query("SELECT * FROM `news` ORDER BY `news_time` DESC LIMIT ".$start.", ".$config['postnews'].";");

		while ($data = $querynews -> fetch()) {
			echo '<div class="b">';
			echo $data['news_closed'] == 0 ? '<img src="/images/img/document_plus.gif" alt="image" /> ' : '<img src="/images/img/document_minus.gif" alt="image" /> ';
			echo '<b><a href="index.php?act=read&amp;id='.$data['news_id'].'">'.$data['news_title'].'</a></b><small> ('.date_fixed($data['news_time']).')</small></div>';

			if (!empty($data['news_image'])) {
				echo '<div class="img"><a href="/upload/news/'.$data['news_image'].'">'.resize_image('upload/news/', $data['news_image'], 75, $data['news_title']).'</a></div>';
			}

			if(stristr($data['news_text'], '[cut]')) {
				$data['news_text'] = current(explode('[cut]', $data['news_text'])).' <a href="index.php?act=read&amp;id='.$data['news_id'].'">Читать далее &raquo;</a>';
			}

			echo '<div>'.bb_code($data['news_text']).'</div>';
			echo '<div style="clear:both;">Добавлено: '.profile($data['news_author']).'<br />';
			echo '<a href="index.php?act=comments&amp;id='.$data['news_id'].'">Комментарии</a> ('.$data['news_comments'].') ';
			echo '<a href="index.php?act=end&amp;id='.$data['news_id'].'">&raquo;</a></div>';
		}

		page_strnavigation('index.php?', $config['postnews'], $start, $total);
	} else {
		show_error('Новостей еще нет!');
	}

	echo '<img src="/images/img/rss.gif" alt="image" /> <a href="rss.php">RSS подписка</a><br />';
	echo '<img src="/images/img/balloon.gif" alt="image" /> <a href="comments.php">Комментарии</a><br />';
break;

############################################################################################
##                                     Чтение новости                                     ##
############################################################################################
case 'read':

	$data = DB::run() -> queryFetch("SELECT * FROM `news` WHERE `news_id`=? LIMIT 1;", array($id));

	if (!empty($data)) {

		if (is_admin()){
			echo '<div class="form"><a href="/admin/news.php?act=edit&amp;id='.$id.'">Редактировать</a> / ';
			echo '<a href="/admin/news.php?act=del&amp;del='.$id.'&amp;uid='.$_SESSION['token'].'" onclick="return confirm(\'Вы действительно хотите удалить данную новость?\')">Удалить</a></div>';
		}

		$config['newtitle'] = $data['news_title'];
		$config['description'] = strip_str($data['news_text']);

		echo '<div class="b"><img src="/images/img/files.gif" alt="image" /> ';
		echo '<b>'.$data['news_title'].'</b><small> ('.date_fixed($data['news_time']).')</small></div>';

		if (!empty($data['news_image'])) {

			echo '<div class="img"><a href="/upload/news/'.$data['news_image'].'">'.resize_image('upload/news/', $data['news_image'], 75, $data['news_title']).'</a></div>';
		}

		$data['news_text'] = str_replace('[cut]', '', $data['news_text']);
		echo '<div>'.bb_code($data['news_text']).'</div>';
		echo '<div style="clear:both;">Добавлено: '.profile($data['news_author']).'</div><br />';

		if ($data['news_comments'] > 0) {
			echo '<div class="act"><img src="/images/img/balloon.gif" alt="image" /> <b>Последние комментарии</b></div>';

			$querycomm = DB::run() -> query("SELECT * FROM `commnews` WHERE `commnews_news_id`=? ORDER BY `commnews_time` DESC LIMIT 5;", array($id));
			$comments = $querycomm -> fetchAll();
			$comments = array_reverse($comments);

			foreach ($comments as $comm) {
				echo '<div class="b">';
				echo '<div class="img">'.user_avatars($comm['commnews_author']).'</div>';

				echo '<b>'.profile($comm['commnews_author']).'</b>';
				echo '<small> ('.date_fixed($comm['commnews_time']).')</small><br />';
				echo user_title($comm['commnews_author']).' '.user_online($comm['commnews_author']).'</div>';

				echo '<div>'.bb_code($comm['commnews_text']).'<br />';

				if (is_admin() || empty($config['anonymity'])) {
					echo '<span class="data">('.$comm['commnews_brow'].', '.$comm['commnews_ip'].')</span>';
				}

				echo '</div>';
			}

			if ($data['news_comments'] > 5) {
				echo '<div class="act"><b><a href="index.php?act=comments&amp;id='.$data['news_id'].'">Все комментарии</a></b> ('.$data['news_comments'].') ';
				echo '<a href="index.php?act=end&amp;id='.$data['news_id'].'">&raquo;</a></div><br />';
			}
		} else {
			show_error('Комментариев еще нет!');
		}

		if (is_user()) {
			if (empty($data['news_closed'])) {
				echo '<div class="form"><form action="index.php?act=add&amp;id='.$id.'&amp;read=1&amp;uid='.$_SESSION['token'].'" method="post">';
				echo '<textarea id="markItUp" cols="25" rows="5" name="msg"></textarea><br />';
				echo '<input type="submit" value="Написать" /></form></div>';

				echo '<br /><a href="#up"><img src="/images/img/ups.gif" alt="image" /></a> ';
				echo '<a href="/pages/rules.php">Правила</a> / ';
				echo '<a href="/pages/smiles.php">Смайлы</a> / ';
				echo '<a href="/pages/tags.php">Теги</a><br /><br />';
			} else {
				show_error('Комментирование данной новости закрыто!');
			}
		} else {
			show_login('Вы не авторизованы, чтобы добавить сообщение, необходимо');
		}
	} else {
		show_error('Ошибка! Выбранная вами новость не существует, возможно она была удалена!');
	}

	echo '<img src="/images/img/back.gif" alt="image" /> <a href="index.php">К новостям</a><br />';
break;

############################################################################################
##                                     Комментарии                                        ##
############################################################################################
case 'comments':

	$datanews = DB::run() -> queryFetch("SELECT * FROM `news` WHERE `news_id`=? LIMIT 1;", array($id));

	if (!empty($datanews)) {
		$config['newtitle'] = 'Комментарии - '.$datanews['news_title'];

		$page = floor(1 + $start / $config['postnews']);
		$config['description'] = 'Комментарии - '.$datanews['news_title'].' (Стр. '.$page.')';

		echo '<img src="/images/img/files.gif" alt="image" /> <b><a href="index.php?act=read&amp;id='.$datanews['news_id'].'">'.$datanews['news_title'].'</a></b><br /><br />';

		echo '<a href="#down"><img src="/images/img/downs.gif" alt="image" /></a> ';
		echo '<a href="index.php?act=end&amp;id='.$id.'">Обновить</a><hr />';

		$total = DB::run() -> querySingle("SELECT count(*) FROM `commnews` WHERE `commnews_news_id`=?;", array($id));

		if ($total > 0) {
			if ($start >= $total) {
				$start = last_page($total, $config['postnews']);
			}

			$is_admin = is_admin();
			if ($is_admin) {
				echo '<form action="index.php?act=del&amp;id='.$id.'&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';
			}

			$querycomm = DB::run() -> query("SELECT * FROM `commnews` WHERE `commnews_news_id`=? ORDER BY `commnews_time` ASC LIMIT ".$start.", ".$config['postnews'].";", array($id));

			while ($data = $querycomm -> fetch()) {

				echo '<div class="b">';
				echo '<div class="img">'.user_avatars($data['commnews_author']).'</div>';

				if ($is_admin) {
					echo '<span class="imgright"><input type="checkbox" name="del[]" value="'.$data['commnews_id'].'" /></span>';
				}

				echo '<b>'.profile($data['commnews_author']).'</b>';
				echo '<small> ('.date_fixed($data['commnews_time']).')</small><br />';
				echo user_title($data['commnews_author']).' '.user_online($data['commnews_author']).'</div>';

				echo '<div>'.bb_code($data['commnews_text']).'<br />';

				if (is_admin() || empty($config['anonymity'])) {
					echo '<span class="data">('.$data['commnews_brow'].', '.$data['commnews_ip'].')</span>';
				}

				echo '</div>';
			}

			if ($is_admin) {
				echo '<span class="imgright"><input type="submit" value="Удалить выбранное" /></span></form>';
			}

			page_strnavigation('index.php?act=comments&amp;id='.$id.'&amp;', $config['postnews'], $start, $total);

		} else {
			show_error('Комментариев еще нет!');
		}

		if (is_user()) {
			if (empty($datanews['news_closed'])) {
				echo '<div class="form"><form action="index.php?act=add&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'" method="post">';
				echo '<textarea id="markItUp" cols="25" rows="5" name="msg"></textarea><br />';
				echo '<input type="submit" value="Написать" /></form></div>';

				echo '<br /><a href="#up"><img src="/images/img/ups.gif" alt="image" /></a> ';
				echo '<a href="/pages/rules.php">Правила</a> / ';
				echo '<a href="/pages/smiles.php">Смайлы</a> / ';
				echo '<a href="/pages/tags.php">Теги</a><br /><br />';
			} else {
				show_error('Комментирование данной новости закрыто!');
			}
		} else {
			show_login('Вы не авторизованы, чтобы добавить сообщение, необходимо');
		}
	} else {
		show_error('Ошибка! Выбранная новость не существует, возможно она была удалена!');
	}

	echo '<img src="/images/img/back.gif" alt="image" /> <a href="index.php">К новостям</a><br />';
break;

############################################################################################
##                                Добавление комментариев                                 ##
############################################################################################
case 'add':

	$uid = (!empty($_GET['uid'])) ? check($_GET['uid']) : 0;
	$msg = (isset($_POST['msg'])) ? check($_POST['msg']) : '';

	if (is_user()) {

		$data = DB::run() -> queryFetch("SELECT * FROM `news` WHERE `news_id`=? LIMIT 1;", array($id));

		$validation = new Validation;

		$validation -> addRule('equal', array($uid, $_SESSION['token']), 'Неверный идентификатор сессии, повторите действие!')
			-> addRule('equal', array(is_quarantine($log), true), 'Карантин! Вы не можете писать в течении '.round($config['karantin'] / 3600).' часов!')
			-> addRule('equal', array(is_flood($log), true), 'Антифлуд! Разрешается комментировать раз в '.flood_period().' сек!')
			-> addRule('not_empty', $data, 'Выбранной новости не существует, возможно она было удалена!')
			-> addRule('string', $msg, 'Слишком длинный или короткий комментарий!', true, 5, 1000)
			-> addRule('empty', $data['news_closed'], 'Комментирование данной новости запрещено!');

		if ($validation->run(3)) {

			$msg = antimat($msg);

			DB::run() -> query("INSERT INTO `commnews` (`commnews_news_id`, `commnews_text`, `commnews_author`, `commnews_time`, `commnews_ip`, `commnews_brow`) VALUES (?, ?, ?, ?, ?, ?);", array($id, $msg, $log, SITETIME, $ip, $brow));

			DB::run() -> query("DELETE FROM `commnews` WHERE `commnews_news_id`=? AND `commnews_time` < (SELECT MIN(`commnews_time`) FROM (SELECT `commnews_time` FROM `commnews` WHERE `commnews_news_id`=? ORDER BY `commnews_time` DESC LIMIT ".$config['maxkommnews'].") AS del);", array($id, $id));

			DB::run() -> query("UPDATE `news` SET `news_comments`=`news_comments`+1 WHERE `news_id`=?;", array($id));
			DB::run() -> query("UPDATE `users` SET `users_allcomments`=`users_allcomments`+1, `users_point`=`users_point`+1, `users_money`=`users_money`+5 WHERE `users_login`=?", array($log));

			notice('Комментарий успешно добавлен!');

			if (isset($_GET['read'])) {
				redirect("index.php?act=read&id=$id");
			}

			redirect("index.php?act=end&id=$id");

		} else {
			show_error($validation->errors);
		}
	} else {
		show_login('Вы не авторизованы, чтобы добавить сообщение, необходимо');
	}

	echo '<img src="/images/img/reload.gif" alt="image" /> <a href="index.php?act=comments&amp;id='.$id.'&amp;start='.$start.'">Вернуться</a><br />';
	echo '<img src="/images/img/back.gif" alt="image" /> <a href="index.php">К новостям</a><br />';
break;

############################################################################################
##                                 Удаление комментариев                                  ##
############################################################################################
case 'del':

	$uid = check($_GET['uid']);
	$del = (isset($_POST['del'])) ? intar($_POST['del']) : 0;

	if (is_admin()) {
		if ($uid == $_SESSION['token']) {
			if (!empty($del)) {

				$del = implode(',', $del);

				$delcomments = DB::run() -> exec("DELETE FROM `commnews` WHERE `commnews_id` IN (".$del.") AND `commnews_news_id`=".$id.";");
				DB::run() -> query("UPDATE `news` SET `news_comments`=`news_comments`-? WHERE `news_id`=?;", array($delcomments, $id));

				notice('Выбранные комментарии успешно удалены!');
				redirect("index.php?act=comments&id=$id&start=$start");

			} else {
				show_error('Ошибка! Отстутствуют выбранные комментарии для удаления!');
			}
		} else {
			show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
		}
	} else {
		show_error('Ошибка! Удалять комментарии могут только модераторы!');
	}

	echo '<img src="/images/img/reload.gif" alt="image" /> <a href="index.php?act=comments&amp;id='.$id.'&amp;start='.$start.'">Вернуться</a><br />';
	echo '<img src="/images/img/back.gif" alt="image" /> <a href="index.php">К новостям</a><br />';
break;

############################################################################################
##                             Переадресация на последнюю страницу                        ##
############################################################################################
case 'end':

	$query = DB::run() -> queryFetch("SELECT count(*) as `total_comments` FROM `commnews` WHERE `commnews_news_id`=? LIMIT 1;", array($id));

	if (!empty($query)) {
		$total_comments = (empty($query['total_comments'])) ? 1 : $query['total_comments'];
		$end = last_page($total_comments, $config['postnews']);

		redirect("index.php?act=comments&id=$id&start=$end");

	} else {
		show_error('Ошибка! Данной новости не существует!');
	}

	echo '<img src="/images/img/back.gif" alt="image" /> <a href="index.php">К новостям</a><br />';
break;

default:
	redirect("index.php");
endswitch;

include_once ('../themes/footer.php');
?>
