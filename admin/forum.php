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

if (isset($_GET['act'])) {
	$act = check($_GET['act']);
} else {
	$act = 'index';
}
if (isset($_GET['fid'])) {
	$fid = abs(intval($_GET['fid']));
} else {
	$fid = 0;
}
if (isset($_GET['tid'])) {
	$tid = abs(intval($_GET['tid']));
} else {
	$tid = 0;
}
if (isset($_GET['start'])) {
	$start = abs(intval($_GET['start']));
} else {
	$start = 0;
}

if (is_admin()) {
	show_title('Управление форумом');

	switch ($act):
	############################################################################################
	##                                    Главная страница                                    ##
	############################################################################################
		case 'index':

			$queryforum = DB::run() -> query("SELECT * FROM `forums` ORDER BY `forums_order` ASC;");
			$forums = $queryforum -> fetchAll();

			if (count($forums) > 0) {
				$output = array();

				foreach ($forums as $row) {
					$id = $row['forums_id'];
					$fp = $row['forums_parent'];
					$output[$fp][$id] = $row;
				}

				echo '<a href="#down"><img src="/images/img/downs.gif" alt="Вниз" /></a> ';
				echo '<a href="/forum/index.php">Обзор форума</a><hr />';

				foreach($output[0] as $key => $data) {
					echo '<div class="b"><img src="/images/img/forums.gif" alt="image" /> ';
					echo '<b>'.$data['forums_order'].'. <a href="forum.php?act=forum&amp;fid='.$data['forums_id'].'">'.$data['forums_title'].'</a></b> ('.$data['forums_topics'].'/'.$data['forums_posts'].')';

					if (!empty($data['forums_desc'])) {
						echo '<br /><small>'.$data['forums_desc'].'</small>';
					}

					if (is_admin(array(101))) {
						echo '<br /><a href="forum.php?act=editforum&amp;fid='.$data['forums_id'].'">Редактировать</a> / ';
						echo '<a href="forum.php?act=prodelforum&amp;fid='.$data['forums_id'].'">Удалить</a>';
					}

					echo '</div><div>';
					// ----------------------------------------------------//
					if (isset($output[$key])) {
						foreach($output[$key] as $datasub) {
							echo '<img src="/images/img/topics-small.gif" alt="image" /> ';
							echo '<b>'.$datasub['forums_order'].'. <a href="forum.php?act=forum&amp;fid='.$datasub['forums_id'].'">'.$datasub['forums_title'].'</a></b>  ('.$datasub['forums_topics'].'/'.$datasub['forums_posts'].') ';
							if (is_admin(array(101))) {
								echo '(<a href="forum.php?act=editforum&amp;fid='.$datasub['forums_id'].'">Редактировать</a> / ';
								echo '<a href="forum.php?act=prodelforum&amp;fid='.$datasub['forums_id'].'">Удалить</a>)';
							}
							echo '<br />';
						}
					}
					// ----------------------------------------------------//
					if ($data['forums_last_id'] > 0) {
						echo 'Тема: <a href="forum.php?act=topic&amp;tid='.$data['forums_last_id'].'">'.$data['forums_last_themes'].'</a><br />';
						echo 'Сообщение: '.nickname($data['forums_last_user']).' ('.date_fixed($data['forums_last_time']).')';
					} else {
						echo 'Темы еще не созданы!';
					}
					echo '</div>';
				}
			} else {
				show_error('Разделы форума еще не созданы!');
			}

			if (is_admin(array(101))) {
				echo '<hr /><form action="forum.php?act=addforum&amp;uid='.$_SESSION['token'].'" method="post">';
				echo 'Заголовок:<br />';
				echo '<input type="text" name="title" maxlength="50" />';
				echo '<input type="submit" value="Создать раздел" /></form><hr />';

				echo '<img src="/images/img/reload.gif" alt="image" /> <a href="forum.php?act=restatement&amp;uid='.$_SESSION['token'].'">Пересчитать</a><br />';
			}

		break;

		############################################################################################
		##                                    Пересчет счетчиков                                  ##
		############################################################################################
		case 'restatement':

			$uid = check($_GET['uid']);

			if (is_admin(array(101))) {
				if ($uid == $_SESSION['token']) {
					restatement('forum');

					$_SESSION['note'] = 'Все данные успешно пересчитаны!';
					redirect("forum.php");

				} else {
					show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
				}
			} else {
				show_error('Ошибка! Пересчитывать сообщения могут только суперадмины!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="forum.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                                    Добавление разделов                                 ##
		############################################################################################
		case 'addforum':

			$uid = check($_GET['uid']);
			$title = check($_POST['title']);

			if (is_admin(array(101))) {
				if ($uid == $_SESSION['token']) {
					if (utf_strlen($title) >= 3 && utf_strlen($title) <= 50) {
						$maxorder = DB::run() -> querySingle("SELECT IFNULL(MAX(`forums_order`),0)+1 FROM `forums`;");
						DB::run() -> query("INSERT INTO `forums` (`forums_order`, `forums_title`) VALUES (?, ?);", array($maxorder, $title));

						$_SESSION['note'] = 'Новый раздел успешно добавлен!';
						redirect("forum.php");

					} else {
						show_error('Ошибка! Слишком длинное или короткое название раздела!');
					}
				} else {
					show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
				}
			} else {
				show_error('Ошибка! Добавлять разделы могут только суперадмины!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="forum.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                          Подготовка к редактированию разделов                          ##
		############################################################################################
		case 'editforum':

			if (is_admin(array(101))) {
				$forums = DB::run() -> queryFetch("SELECT * FROM `forums` WHERE `forums_id`=? LIMIT 1;", array($fid));
				if (!empty($forums)) {
					echo '<b><big>Редактирование</big></b><br /><br />';

					echo '<div class="form">';
					echo '<form action="forum.php?act=addeditforum&amp;fid='.$fid.'&amp;uid='.$_SESSION['token'].'" method="post">';
					echo 'Раздел: <br />';
					echo '<input type="text" name="title" maxlength="50" value="'.$forums['forums_title'].'" /><br />';

					$query = DB::run() -> query("SELECT `forums_id`, `forums_title`, `forums_parent` FROM `forums` WHERE `forums_parent`=? ORDER BY `forums_order` ASC;", array(0));
					$section = $query -> fetchAll();

					echo 'Родительский форум:<br />';
					echo '<select name="parent">';
					echo '<option value="0">Основной форум</option>';

					foreach ($section as $data) {
						if ($fid != $data['forums_id']) {
							$selected = ($forums['forums_parent'] == $data['forums_id']) ? ' selected="selected"' : '';
							echo '<option value="'.$data['forums_id'].'"'.$selected.'>'.$data['forums_title'].'</option>';
						}
					}
					echo '</select><br />';

					echo 'Описание: <br />';
					echo '<input type="text" name="desc" maxlength="100" value="'.$forums['forums_desc'].'" /><br />';

					echo 'Положение: <br />';
					echo '<input type="text" name="order" maxlength="2" value="'.$forums['forums_order'].'" /><br />';

					echo 'Закрыть форум: ';
					$checked = ($forums['forums_closed'] == 1) ? ' checked="checked"' : '';
					echo '<input name="closed" type="checkbox" value="1"'.$checked.' /><br />';

					echo '<input type="submit" value="Изменить" /></form></div><br />';
				} else {
					show_error('Ошибка! Данного раздела не существует!');
				}
			} else {
				show_error('Ошибка! Изменять разделы могут только суперадмины!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="forum.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                                 Редактирование разделов                                ##
		############################################################################################
		case 'addeditforum':

			$uid = check($_GET['uid']);
			$title = check($_POST['title']);
			$desc = check($_POST['desc']);
			$parent = abs(intval($_POST['parent']));
			$order = abs(intval($_POST['order']));
			$closed = (empty($_POST['closed'])) ? 0 : 1;

			if (is_admin(array(101))) {
				if ($uid == $_SESSION['token']) {
					if (utf_strlen($title) >= 3 && utf_strlen($title) <= 50) {
						if (utf_strlen($desc) <= 100) {
							if ($fid != $parent) {
								$forums = DB::run() -> queryFetch("SELECT `forums_id` FROM `forums` WHERE `forums_parent`=? LIMIT 1;", array($fid));

								if (empty($forums) || empty($parent)) {
									DB::run() -> query("UPDATE `forums` SET `forums_order`=?, `forums_parent`=?, `forums_title`=?, `forums_desc`=?, `forums_closed`=? WHERE `forums_id`=?;", array($order, $parent, $title, $desc, $closed, $fid));

									$_SESSION['note'] = 'Раздел успешно отредактирован!';
									redirect("forum.php");

								} else {
									show_error('Ошибка! Данный раздел имеет подфорумы!');
								}
							} else {
								show_error('Ошибка! Недопустимый выбор родительского форума!');
							}
						} else {
							show_error('Ошибка! Слишком длинный текст описания раздела!');
						}
					} else {
						show_error('Ошибка! Слишком длинное или короткое название раздела!');
					}
				} else {
					show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
				}
			} else {
				show_error('Ошибка! Изменять разделы могут только суперадмины!');
			}

			echo '<img src="/images/img/reload.gif" alt="image" /> <a href="forum.php?act=editforum&amp;fid='.$fid.'">Вернуться</a><br />';
			echo '<img src="/images/img/back.gif" alt="image" /> <a href="forum.php">В Форум</a><br />';
		break;

		############################################################################################
		##                                  Подтвержение удаления                                 ##
		############################################################################################
		case 'prodelforum':

			if (is_admin(array(101))) {
				$forums = DB::run() -> queryFetch("SELECT `f1`.*, count(`f2`.`forums_id`) AS `subcnt` FROM `forums` `f1` LEFT JOIN `forums` `f2` ON `f2`.`forums_parent` = `f1`.`forums_id` WHERE `f1`.`forums_id`=? GROUP BY `forums_id` LIMIT 1;", array($fid));

				if (!empty($forums['forums_id'])) {
					if (empty($forums['subcnt'])) {
						echo 'Вы уверены что хотите удалить раздел <b>'.$forums['forums_title'].'</b> в форуме?<br />';
						echo '<img src="/images/img/error.gif" alt="image" /> <b><a href="forum.php?act=delforum&amp;fid='.$fid.'&amp;uid='.$_SESSION['token'].'">Да, уверен!</a></b><br /><br />';
					} else {
						show_error('Ошибка! Данный раздел имеет подфорумы!');
					}
				} else {
					show_error('Ошибка! Данного раздела не существует!');
				}
			} else {
				show_error('Ошибка! Удалять разделы могут только суперадмины!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="forum.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                                    Удаление раздела                                    ##
		############################################################################################
		case 'delforum':

			$uid = check($_GET['uid']);

			if (is_admin(array(101))) {
				if ($uid == $_SESSION['token']) {
					$forums = DB::run() -> queryFetch("SELECT `f1`.*, count(`f2`.`forums_id`) AS `subcnt` FROM `forums` `f1` LEFT JOIN `forums` `f2` ON `f2`.`forums_parent` = `f1`.`forums_id` WHERE `f1`.`forums_id`=? GROUP BY `forums_id` LIMIT 1;", array($fid));

					if (!empty($forums['forums_id'])) {
						if (empty($forums['subcnt'])) {

							// ------ Удаление загруженных файлов -------//
							$querytopics = DB::run() -> query("SELECT `topics_id` FROM `topics` WHERE `topics_forums_id`=?;", array($fid));
							$topics = $querytopics->fetchAll(PDO::FETCH_COLUMN);

							if (!empty($topics)){
								$delId = implode(',', $topics);

								foreach($topics as $delDir){
									removeDir(BASEDIR.'/upload/forum/'.$delDir);
								}
								DB::run() -> query("DELETE FROM `files_forum` WHERE `file_topics_id` IN (".$delId.");");
							}
							// ------ Удаление загруженных файлов -------//

							DB::run() -> query("DELETE FROM `posts` WHERE `posts_forums_id`=?;", array($fid));
							DB::run() -> query("DELETE FROM `topics` WHERE `topics_forums_id`=?;", array($fid));
							DB::run() -> query("DELETE FROM `forums` WHERE `forums_id`=?;", array($fid));
							DB::run() -> query("DELETE FROM `bookmarks` WHERE `book_forum`=?;", array($fid));

							$_SESSION['note'] = 'Раздел успешно удален!';
							redirect("forum.php");

						} else {
							show_error('Ошибка! Данный раздел имеет подфорумы!');
						}
					} else {
						show_error('Ошибка! Данного раздела не существует!');
					}
				} else {
					show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
				}
			} else {
				show_error('Ошибка! Удалять разделы могут только суперадмины!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="forum.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                                    Просмотр тем в разделе                              ##
		############################################################################################
		case 'forum':

			$forums = DB::run() -> queryFetch("SELECT * FROM `forums` WHERE `forums_id`=? LIMIT 1;", array($fid));

			if (!empty($forums)) {
				echo '<a href="#down"><img src="/images/img/downs.gif" alt="Вниз" /></a> ';
				echo '<a href="forum.php">Форум</a> / ';
				echo '<a href="/forum/forum.php?fid='.$fid.'&amp;start='.$start.'">Обзор раздела</a><br /><br />';

				echo '<img src="/images/img/themes.gif" alt="image" /> <b>'.$forums['forums_title'].'</b><hr />';

				$total = DB::run() -> querySingle("SELECT count(*) FROM `topics` WHERE `topics_forums_id`=?;", array($fid));

				if ($total > 0) {
					if ($start >= $total) {
						$start = 0;
					}

					$querytopic = DB::run() -> query("SELECT * FROM `topics` WHERE `topics_forums_id`=? ORDER BY `topics_locked` DESC, `topics_last_time` DESC LIMIT ".$start.", ".$config['forumtem'].";", array($fid));

					echo '<form action="forum.php?act=deltopics&amp;fid='.$fid.'&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';

					echo '<div class="form">';
					echo '<input type="checkbox" id="all" onchange="var o=this.form.elements;for(var i=0;i&lt;o.length;i++)o[i].checked=this.checked" /> <b><label for="all">Отметить все</label></b>';
					echo '</div>';

					while ($data = $querytopic -> fetch()) {
						echo '<div class="b">';

						if ($data['topics_locked'] == 1) {
							echo '<img src="/images/img/lock.gif" alt="image" /> ';
						} elseif ($data['topics_closed'] == 1) {
							echo '<img src="/images/img/closed.gif" alt="image" /> ';
						} else {
							echo '<img src="/images/img/topics.gif" alt="image" /> ';
						}

						echo '<b><a href="forum.php?act=topic&amp;tid='.$data['topics_id'].'">'.$data['topics_title'].'</a></b> ('.$data['topics_posts'].')<br />';

						echo '<input type="checkbox" name="del[]" value="'.$data['topics_id'].'" /> ';

						echo '<a href="forum.php?act=edittopic&amp;tid='.$data['topics_id'].'&amp;start='.$start.'">Редактировать</a> / ';
						echo '<a href="forum.php?act=movetopic&amp;tid='.$data['topics_id'].'&amp;start='.$start.'">Переместить</a></div>';

						echo '<div>Страницы: ';
						forum_navigation('forum.php?act=topic&amp;tid='.$data['topics_id'].'&amp;', $config['forumpost'], $data['topics_posts']);
						echo 'Сообщение: '.nickname($data['topics_last_user']).' ('.date_fixed($data['topics_last_time']).')</div>';
					}

					echo '<br /><input type="submit" value="Удалить выбранное" /></form>';

					page_strnavigation('forum.php?act=forum&amp;fid='.$fid.'&amp;', $config['forumtem'], $start, $total);
				} else {
					if (empty($forums['forums_closed'])) {
						show_error('Тем еще нет, будь первым!');
					}
				}

				if (!empty($forums['forums_closed'])) {
					show_error('В данном разделе запрещено создавать темы!');
				}
			} else {
				show_error('Ошибка! Данного раздела не существует!');
			}

			echo '<img src="/images/img/reload.gif" alt="image" /> <a href="forum.php">К форумам</a><br />';
		break;

		############################################################################################
		##                            Подготовка к редактированию темы                            ##
		############################################################################################
		case 'edittopic':

			$topics = DB::run() -> queryFetch("SELECT `topics`.*, `forums`.`forums_id` FROM `topics` LEFT JOIN `forums` ON `topics`.`topics_forums_id`=`forums`.`forums_id` WHERE `topics_id`=? LIMIT 1;", array($tid));

			if (!empty($topics)) {

				echo '<b><big>Редактирование</big></b><br /><br />';

				echo '<div class="form">';
				echo '<form action="forum.php?act=addedittopic&amp;fid='.$topics['forums_id'].'&amp;tid='.$tid.'&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';
				echo 'Тема: <br />';
				echo '<input type="text" name="title" size="50" maxlength="50" value="'.$topics['topics_title'].'" /><br />';
				echo 'Кураторы темы: <br />';
				echo '<input type="text" name="mod" size="50" maxlength="100" value="'.$topics['topics_mod'].'" /><br />';

				echo 'Объявление:<br />';
				echo '<textarea id="markItUp" cols="25" rows="5" name="note">'.yes_br(nosmiles($topics['topics_note'])).'</textarea><br />';

				echo 'Закрепить тему: ';
				$checked = ($topics['topics_locked'] == 1) ? ' checked="checked"' : '';
				echo '<input name="locked" type="checkbox" value="1"'.$checked.' /><br />';

				echo 'Закрыть тему: ';
				$checked = ($topics['topics_closed'] == 1) ? ' checked="checked"' : '';
				echo '<input name="closed" type="checkbox" value="1"'.$checked.' /><br />';

				echo '<br /><input type="submit" value="Изменить" /></form></div><br />';
			} else {
				show_error('Ошибка! Данной темы не существует!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="forum.php?act=forum&amp;fid='.$topics['forums_id'].'&amp;start='.$start.'">Вернуться</a><br />';
		break;

		############################################################################################
		##                                     Редактирование темы                                ##
		############################################################################################
		case 'addedittopic':

			$uid = check($_GET['uid']);
			$title = check($_POST['title']);
			$mod = check($_POST['mod']);
			$note = check($_POST['note']);
			$locked = (empty($_POST['locked'])) ? 0 : 1;
			$closed = (empty($_POST['closed'])) ? 0 : 1;

			if ($uid == $_SESSION['token']) {
				if (utf_strlen($title) >= 5 && utf_strlen($title) <= 50) {
					if (utf_strlen($note) <= 250) {

						$mod = implode(',', preg_split('/[\s]*[,][\s]*/', $mod));
						$note = smiles(no_br($note));

						DB::run() -> query("UPDATE `topics` SET `topics_title`=?, `topics_closed`=?, `topics_locked`=?, `topics_mod`=?, `topics_note`=? WHERE `topics_id`=?;", array($title, $closed, $locked, $mod, $note, $tid));

						if ($locked == 1) {
							$start = 0;
						}
						$_SESSION['note'] = 'Тема успешно отредактирована!';
						redirect("forum.php?act=forum&fid=$fid&start=$start");

					} else {
						show_error('Ошибка! Слишком длинное объявление (Не более 250 символов)!');
					}
				} else {
					show_error('Ошибка! Слишком длинное или короткое название темы!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="forum.php?act=edittopic&amp;tid='.$tid.'&amp;start='.$start.'">Вернуться</a><br />';
			echo '<img src="/images/img/reload.gif" alt="image" /> <a href="forum.php?act=forum&amp;fid='.$fid.'&amp;start='.$start.'">К темам</a><br />';
		break;

		############################################################################################
		##                               Подготовка к перемещению темы                            ##
		############################################################################################
		case 'movetopic':

			$topics = DB::run() -> queryFetch("SELECT * FROM `topics` WHERE `topics_id`=? LIMIT 1;", array($tid));
			if (!empty($topics)) {
				echo '<img src="/images/img/topics.gif" alt="image" /> <b>'.$topics['topics_title'].'</b> (Автор темы: '.nickname($topics['topics_author']).')<br /><br />';

				$queryforum = DB::run() -> query("SELECT `forums_id`, `forums_parent`, `forums_title` FROM `forums` ORDER BY `forums_order` ASC;");
				$forums = $queryforum -> fetchAll();

				if (count($forums) > 0) {
					$output = array();
					foreach ($forums as $row) {
						$i = $row['forums_id'];
						$p = $row['forums_parent'];
						$output[$p][$i] = $row;
					}

					echo '<div class="form"><form action="forum.php?act=addmovetopic&amp;fid='.$topics['topics_forums_id'].'&amp;tid='.$tid.'&amp;uid='.$_SESSION['token'].'" method="post">';

					echo 'Выберите раздел для перемещения:<br />';
					echo '<select name="section">';

					foreach ($output[0] as $key => $data) {
						if ($topics['topics_forums_id'] != $data['forums_id']) {
							echo '<option value="'.$data['forums_id'].'">'.$data['forums_title'].'</option>';
						}

						if (isset($output[$key])) {
							foreach($output[$key] as $datasub) {
								if ($topics['topics_forums_id'] != $datasub['forums_id']) {
									echo '<option value="'.$datasub['forums_id'].'">– '.$datasub['forums_title'].'</option>';
								}
							}
						}
					}

					echo '</select>';

					echo '<input type="submit" value="Переместить" /></form></div><br />';
				} else {
					show_error('Разделы форума еще не созданы!');
				}
			} else {
				show_error('Ошибка! Данной темы не существует!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="forum.php?act=forum&amp;fid='.$topics['topics_forums_id'].'&amp;start='.$start.'">Вернуться</a><br />';
		break;

		############################################################################################
		##                                    Перемещение темы                                    ##
		############################################################################################
		case 'addmovetopic':

			$uid = check($_GET['uid']);
			$section = abs(intval($_POST['section']));

			if ($uid == $_SESSION['token']) {
				$forums = DB::run() -> queryFetch("SELECT * FROM `forums` WHERE `forums_id`=? LIMIT 1;", array($section));
				$topics = DB::run() -> queryFetch("SELECT * FROM `topics` WHERE `topics_id`=? LIMIT 1;", array($tid));

				if (!empty($forums)) {

					// Обновление номера раздела
					DB::run() -> query("UPDATE `topics` SET `topics_forums_id`=? WHERE `topics_id`=?;", array($section, $tid));
					DB::run() -> query("UPDATE `posts` SET `posts_forums_id`=? WHERE `posts_topics_id`=?;", array($section, $tid));

					// Ищем последние темы в форумах для обновления списка последних тем
					$oldlast = DB::run() -> queryFetch("SELECT * FROM `topics` WHERE `topics_forums_id`=? ORDER BY `topics_last_time` DESC LIMIT 1;", array($topics['topics_forums_id']));
					$newlast = DB::run() -> queryFetch("SELECT * FROM `topics` WHERE `topics_forums_id`=? ORDER BY `topics_last_time` DESC LIMIT 1;", array($section));

					DB::run() -> query("UPDATE `forums` SET `forums_last_id`=?, `forums_last_themes`=?, `forums_last_user`=?, `forums_last_time`=? WHERE `forums_id`=?;", array($oldlast['topics_id'], $oldlast['topics_title'], $oldlast['topics_last_user'], $oldlast['topics_last_time'], $oldlast['topics_forums_id']));

					DB::run() -> query("UPDATE `forums` SET `forums_last_id`=?, `forums_last_themes`=?, `forums_last_user`=?, `forums_last_time`=? WHERE `forums_id`=?;", array($newlast['topics_id'], $newlast['topics_title'], $newlast['topics_last_user'], $newlast['topics_last_time'], $newlast['topics_forums_id']));
					// Обновление закладок
					DB::run() -> query("UPDATE `bookmarks` SET `book_forum`=? WHERE `book_topic`=?;", array($section, $tid));

					$_SESSION['note'] = 'Тема успешно перемещена!';
					redirect("forum.php?act=forum&fid=$section");

				} else {
					show_error('Ошибка! Выбранного раздела не существует!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="forum.php?act=movetopic&amp;tid='.$tid.'">Вернуться</a><br />';
			echo '<img src="/images/img/reload.gif" alt="image" /> <a href="forum.php?act=forum&amp;fid='.$fid.'">К темам</a><br />';
		break;

		############################################################################################
		##                                     Удаление тем                                       ##
		############################################################################################
		case 'deltopics':

			$uid = check($_GET['uid']);
			if (isset($_POST['del'])) {
				$del = intar($_POST['del']);
			} elseif (isset($_GET['del'])) {
				$del = array(abs(intval($_GET['del'])));
			} else {
				$del = 0;
			}

			if ($uid == $_SESSION['token']) {
				if (!empty($del)) {
					$delId = implode(',', $del);

					// ------ Удаление загруженных файлов -------//
					foreach($del as $delDir){
						removeDir(BASEDIR.'/upload/forum/'.$delDir);
					}
					DB::run() -> query("DELETE FROM `files_forum` WHERE `file_topics_id` IN (".$delId.");");
					// ------ Удаление загруженных файлов -------//

					$deltopics = DB::run() -> exec("DELETE FROM `topics` WHERE `topics_id` IN (".$delId.");");
					$delposts = DB::run() -> exec("DELETE FROM `posts` WHERE `posts_topics_id` IN (".$delId.");");

					// Удаление закладок
					DB::run() -> query("DELETE FROM `bookmarks` WHERE `book_topic` IN (".$delId.");");

					// Обновление счетчиков
					DB::run() -> query("UPDATE `forums` SET `forums_topics`=`forums_topics`-?, `forums_posts`=`forums_posts`-? WHERE `forums_id`=?;", array($deltopics, $delposts, $fid));

					// ------------------------------------------------------------//
					$oldlast = DB::run() -> queryFetch("SELECT `topics`.*, `forums`.`forums_parent` FROM `topics` LEFT JOIN `forums` ON `topics`.`topics_forums_id`=`forums`.`forums_id` WHERE `topics`.`topics_forums_id`=? ORDER BY `topics`.`topics_last_time` DESC LIMIT 1;", array($fid));

					DB::run() -> query("UPDATE `forums` SET `forums_last_id`=?, `forums_last_themes`=?, `forums_last_user`=?, `forums_last_time`=? WHERE `forums_id`=?;", array($oldlast['topics_id'], $oldlast['topics_title'], $oldlast['topics_last_user'], $oldlast['topics_last_time'], $fid));

					// Обновление родительского форума
					if ($oldlast['forums_parent'] > 0) {
						DB::run() -> query("UPDATE `forums` SET `forums_last_id`=?, `forums_last_themes`=?, `forums_last_user`=?, `forums_last_time`=? WHERE `forums_id`=?;", array($oldlast['topics_id'], $oldlast['topics_title'], $oldlast['topics_last_user'], $oldlast['topics_last_time'], $oldlast['forums_parent']));
					}

					$_SESSION['note'] = 'Выбранные темы успешно удалены!';
					redirect("forum.php?act=forum&fid=$fid&start=$start");

				} else {
					show_error('Ошибка! Отсутствуют выбранные темы форума!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="forum.php?act=forum&amp;fid='.$fid.'&amp;start='.$start.'">Вернуться</a><br />';
		break;

		############################################################################################
		##                                  Закрытие - Закрепление темы                           ##
		############################################################################################
		case 'acttopic':

			$uid = check($_GET['uid']);
			$do = check($_GET['do']);

			if ($uid == $_SESSION['token']) {
				$topics = DB::run() -> queryFetch("SELECT * FROM `topics` WHERE `topics_id`=? LIMIT 1;", array($tid));

				if (!empty($topics)) {
					switch ($do):
						case 'closed':
							DB::run() -> query("UPDATE `topics` SET `topics_closed`=? WHERE `topics_id`=?;", array(1, $tid));
							$_SESSION['note'] = 'Тема успешно закрыта!';
							redirect("forum.php?act=topic&tid=$tid&start=$start");
							break;

						case 'open':
							DB::run() -> query("UPDATE `topics` SET `topics_closed`=? WHERE `topics_id`=?;", array(0, $tid));
							$_SESSION['note'] = 'Тема успешно открыта!';
							redirect("forum.php?act=topic&tid=$tid&start=$start");
							break;

						case 'locked':
							DB::run() -> query("UPDATE `topics` SET `topics_locked`=? WHERE `topics_id`=?;", array(1, $tid));
							$_SESSION['note'] = 'Тема успешно закреплена!';
							redirect("forum.php?act=topic&tid=$tid&start=$start");
							break;

						case 'unlocked':
							DB::run() -> query("UPDATE `topics` SET `topics_locked`=? WHERE `topics_id`=?;", array(0, $tid));
							$_SESSION['note'] = 'Тема успешно откреплена!';
							redirect("forum.php?act=topic&tid=$tid&start=$start");
							break;

						default:
							show_error('Ошибка! Не выбрано действие для темы!');
							endswitch;
					} else {
						show_error('Ошибка! Данной темы не существует!');
					}
				} else {
					show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
				}

				echo '<img src="/images/img/reload.gif" alt="image" /> <a href="forum.php">К форумам</a><br />';
			break;

		############################################################################################
		##                                     Просмотр сообщений                                 ##
		############################################################################################
		case 'topic':
			if (!empty($tid)) {
				$topic = DB::run() -> queryFetch("SELECT `topics`.*, `forums`.`forums_id`, `forums`.`forums_title`, `forums`.`forums_parent` FROM `topics` LEFT JOIN `forums` ON `topics`.`topics_forums_id`=`forums`.`forums_id` WHERE `topics_id`=? LIMIT 1;", array($tid));

				if (!empty($topic)) {
					echo '<a href="#down"><img src="/images/img/downs.gif" alt="Вниз" /></a> ';
					echo '<a href="forum.php">Форум</a> / ';

					if (!empty($topic['forums_parent'])) {
						$forums = DB::run() -> queryFetch("SELECT `forums_id`, `forums_title` FROM `forums` WHERE `forums_id`=? LIMIT 1;", array($topic['forums_parent']));
						echo '<a href="forum.php?fid='.$forums['forums_id'].'">'.$forums['forums_title'].'</a> / ';
					}

					echo '<a href="forum.php?act=forum&amp;fid='.$topic['forums_id'].'">'.$topic['forums_title'].'</a> / ';
					echo '<a href="/forum/topic.php?tid='.$tid.'&amp;start='.$start.'">Обзор темы</a><br /><br />';

					echo '<img src="/images/img/themes.gif" alt="image" /> <b>'.$topic['topics_title'].'</b>';

					if (!empty($topic['topics_mod'])) {
						$topics_mod = explode(',', $topic['topics_mod']);

						echo '<br />Кураторы темы: ';
						foreach ($topics_mod as $mkey => $mval) {
							$comma = (empty($mkey)) ? '' : ', ';
							echo $comma . profile($mval);
						}
					}

	 				if (!empty($topic['topics_note'])){
						echo '<div class="info">'.bb_code($topic['topics_note']).'</div>';
					}

					echo '<hr />';

					if (empty($topic['topics_closed'])) {
						echo '<a href="/admin/forum.php?act=acttopic&amp;do=closed&amp;tid='.$tid.'&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'">Закрыть</a> / ';
					} else {
						echo '<a href="/admin/forum.php?act=acttopic&amp;do=open&amp;tid='.$tid.'&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'">Открыть</a> / ';
					}

					if (empty($topic['topics_locked'])) {
						echo '<a href="/admin/forum.php?act=acttopic&amp;do=locked&amp;tid='.$tid.'&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'">Закрепить</a> / ';
					} else {
						echo '<a href="/admin/forum.php?act=acttopic&amp;do=unlocked&amp;tid='.$tid.'&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'">Открепить</a> / ';
					}

					echo '<a href="/admin/forum.php?act=edittopic&amp;tid='.$tid.'&amp;start='.$start.'">Изменить</a> / ';
					echo '<a href="/admin/forum.php?act=movetopic&amp;tid='.$tid.'">Переместить</a> / ';
					echo '<a href="/admin/forum.php?act=deltopics&amp;fid='.$topic['forums_id'].'&amp;del='.$tid.'&amp;uid='.$_SESSION['token'].'" onclick="return confirm(\'Вы действительно хотите удалить данную тему?\')">Удалить</a><br />';

					$total = DB::run() -> querySingle("SELECT count(*) FROM `posts` WHERE `posts_topics_id`=?;", array($tid));

					if ($total > 0) {
						if ($start >= $total) {
							$start = last_page($total, $config['forumpost']);
						}

						$querypost = DB::run() -> query("SELECT * FROM `posts` WHERE `posts_topics_id`=? ORDER BY `posts_time` ASC LIMIT ".$start.", ".$config['forumpost'].";", array($tid));
						$posts = $querypost->fetchAll();

						// ----- Получение массива файлов ----- //
						$ipdpost = array();
						foreach ($posts as $val) {
							$ipdpost[] = $val['posts_id'];
						}

						$ipdpost = implode(',', $ipdpost);

						$queryfiles = DB::run() -> query("SELECT * FROM `files_forum` WHERE `file_posts_id` IN (".$ipdpost.");");
						$files = $queryfiles->fetchAll();

						if (!empty($files)){
							$forumfiles = array();
							foreach ($files as $file){
								$forumfiles[$file['file_posts_id']][] = $file;
							}
						}
						// ------------------------------------- //

						echo '<form action="forum.php?act=delposts&amp;tid='.$tid.'&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';

						echo '<div align="right" class="form">';
						echo '<b><label for="all">Отметить все</label></b> <input type="checkbox" id="all" onchange="var o=this.form.elements;for(var i=0;i&lt;o.length;i++)o[i].checked=this.checked" />&nbsp;';
						echo '</div>';

						foreach ($posts as $key=>$data){
							$num = ($start + $key + 1);

							echo '<div class="b">';

							echo '<div class="img">'.user_avatars($data['posts_user']).'</div>';
							echo '<span class="imgright"><a href="forum.php?act=editpost&amp;tid='.$tid.'&amp;pid='.$data['posts_id'].'&amp;start='.$start.'">Ред.</a> <input type="checkbox" name="del[]" value="'.$data['posts_id'].'" /></span>';


							echo $num.'. <b>'.profile($data['posts_user']).'</b>  <small>('.date_fixed($data['posts_time']).')</small><br />';
							echo user_title($data['posts_user']).' '.user_online($data['posts_user']).'</div>';

							echo '<div>'.bb_code($data['posts_text']).'<br />';

							// -- Прикрепленные файлы -- //
							if (!empty($forumfiles)) {
								if (isset($forumfiles[$data['posts_id']])){
									echo '<div class="hide"><img src="/images/img/paper-clip.gif" alt="attach" /> <b>Прикрепленные файлы:</b><br />';
									foreach ($forumfiles[$data['posts_id']] as $file){
										$ext = getExtension($file['file_hash']);
										echo '<img src="/images/icons/'.icons($ext).'" alt="image" /> ';

										echo '<a href="/upload/forum/'.$file['file_topics_id'].'/'.$file['file_hash'].'" target="_blank">'.$file['file_name'].'</a> ('.formatsize($file['file_size']).')<br />';
									}
									echo '</div>';
								}
							}
							// --------------------------//

							if (!empty($data['posts_edit'])) {
								echo '<img src="/images/img/exclamation_small.gif" alt="image" /> <small>Отредактировано: '.nickname($data['posts_edit']).' ('.date_fixed($data['posts_edit_time']).')</small><br />';
							}

							echo '<span class="data">('.$data['posts_brow'].', '.$data['posts_ip'].')</span></div>';
						}

						echo '<span class="imgright"><input type="submit" value="Удалить выбранное" /></span></form>';

						page_strnavigation('forum.php?act=topic&amp;tid='.$tid.'&amp;', $config['forumpost'], $start, $total);
					} else {
						show_error('Сообщений еще нет, будь первым!');
					}

					if (empty($topic['topics_closed'])) {
						echo '<div class="form" id="form">';
						echo '<form action="/forum/topic.php?act=add&amp;tid='.$tid.'&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';
						echo 'Сообщение:<br />';
						echo '<textarea id="markItUp" cols="25" rows="5" name="msg"></textarea><br />';
						echo '<input type="submit" value="Написать" />';

						echo '<span class="imgright"><a href="/forum/topic.php?act=addfile&amp;tid='.$tid.'&amp;start='.$start.'">Загрузить файл</a></span>';

						echo '</form></div><br />';

					} else {
						show_error('Данная тема закрыта для обсуждения!');
					}
				} else {
					show_error('Ошибка! Данной темы не существует!');
				}
			} else {
				show_error('Ошибка! Не выбрана тема!');
			}
			echo '<img src="/images/img/reload.gif" alt="image" /> <a href="forum.php">К форумам</a><br />';
		break;

		############################################################################################
		##                                    Удаление сообщений                                  ##
		############################################################################################
		case 'delposts':

			$uid = check($_GET['uid']);
			if (isset($_POST['del'])) {
				$del = intar($_POST['del']);
			} else {
				$del = 0;
			}

			if ($uid == $_SESSION['token']) {
				if (!empty($del)) {
					$topics = DB::run() -> queryFetch("SELECT * FROM `topics` WHERE `topics_id`=? LIMIT 1;", array($tid));
					$del = implode(',', $del);

					// ------ Удаление загруженных файлов -------//
					$queryfiles = DB::run() -> query("SELECT `file_hash` FROM `files_forum` WHERE `file_posts_id` IN (".$del.");");
					$files = $queryfiles->fetchAll(PDO::FETCH_COLUMN);

					if (!empty($files)){
						foreach ($files as $file){
							if (file_exists(BASEDIR.'/upload/forum/'.$topics['topics_id'].'/'.$file)){
								unlink(BASEDIR.'/upload/forum/'.$topics['topics_id'].'/'.$file);
							}
						}
						DB::run() -> query("DELETE FROM `files_forum` WHERE `file_posts_id` IN (".$del.");");
					}
					// ------ Удаление загруженных файлов -------//

					$delposts = DB::run() -> exec("DELETE FROM `posts` WHERE `posts_id` IN (".$del.") AND `posts_topics_id`=".$tid.";");
					DB::run() -> query("UPDATE `topics` SET `topics_posts`=`topics_posts`-? WHERE `topics_id`=?;", array($delposts, $tid));
					DB::run() -> query("UPDATE `forums` SET `forums_posts`=`forums_posts`-? WHERE `forums_id`=?;", array($delposts, $topics['topics_forums_id']));

					$_SESSION['note'] = 'Выбранные сообщения успешно удалены!';
					redirect("forum.php?act=topic&tid=$tid&start=$start");

				} else {
					show_error('Ошибка! Отсутствуют выбранные сообщения!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="forum.php?act=topic&amp;tid='.$tid.'&amp;start='.$start.'">Вернуться</a><br />';
		break;

		############################################################################################
		##                                   Подготовка к редактированию                          ##
		############################################################################################
		case 'editpost':

			$pid = abs(intval($_GET['pid']));

			$post = DB::run() -> queryFetch("SELECT * FROM `posts` WHERE `posts_id`=? LIMIT 1;", array($pid));
			if (!empty($post)) {

				echo '<img src="/images/img/edit.gif" alt="image" /> <b>'.nickname($post['posts_user']).'</b> <small>('.date_fixed($post['posts_time']).')</small><br /><br />';

				echo '<div class="form" id="form">';
				echo '<form action="forum.php?act=addeditpost&amp;tid='.$post['posts_topics_id'].'&amp;pid='.$pid.'&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';
				echo 'Редактирование сообщения:<br />';
				echo '<textarea id="markItUp" cols="25" rows="10" name="msg">'.yes_br(nosmiles($post['posts_text'])).'</textarea><br />';

				$queryfiles = DB::run() -> query("SELECT * FROM `files_forum` WHERE `file_posts_id`=?;", array($pid));
				$files = $queryfiles->fetchAll();

				if (!empty($files)){
					echo '<img src="/images/img/paper-clip.gif" alt="attach" /> <b>Удаление файлов:</b><br />';
					foreach ($files as $file){
						echo '<input type="checkbox" name="delfile[]" value="'.$file['file_id'].'" /> ';
						echo '<a href="/upload/forum/'.$file['file_topics_id'].'/'.$file['file_hash'].'" target="_blank">'.$file['file_name'].'</a> ('.formatsize($file['file_size']).')<br />';
					}
					echo '<br />';
				}

				echo '<input value="Редактировать" name="do" type="submit" /></form></div><br />';
			} else {
				show_error('Ошибка! Данного сообщения не существует!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="forum.php?act=topic&amp;tid='.$tid.'&amp;start='.$start.'">Вернуться</a><br />';
		break;

		############################################################################################
		##                                    Редактирование сообщения                            ##
		############################################################################################
		case 'addeditpost':

			$uid = check($_GET['uid']);
			$pid = abs(intval($_GET['pid']));
			$msg = check($_POST['msg']);

			if (isset($_POST['delfile'])) {
				$del = intar($_POST['delfile']);
			} else {
				$del = 0;
			}

			if ($uid == $_SESSION['token']) {
				if (utf_strlen($msg) >= 5 && utf_strlen($msg) <= $config['forumtextlength']) {
					$post = DB::run() -> queryFetch("SELECT * FROM `posts` WHERE `posts_id`=? LIMIT 1;", array($pid));
					if (!empty($post)) {
						$msg = smiles(no_br($msg));

						DB::run() -> query("UPDATE `posts` SET `posts_text`=?, `posts_edit`=?, `posts_edit_time`=? WHERE `posts_id`=?;", array($msg, $log, SITETIME, $pid));

						// ------ Удаление загруженных файлов -------//
						if (!empty($del)) {
							$del = implode(',', $del);

							$queryfiles = DB::run() -> query("SELECT * FROM `files_forum` WHERE `file_posts_id`=? AND `file_id` IN (".$del.");", array($pid));
							$files = $queryfiles->fetchAll();

							if (!empty($files)){
								foreach ($files as $file){
									if (file_exists(BASEDIR.'/upload/forum/'.$file['file_topics_id'].'/'.$file['file_hash'])){
										unlink(BASEDIR.'/upload/forum/'.$file['file_topics_id'].'/'.$file['file_hash']);
									}
								}
								DB::run() -> query("DELETE FROM `files_forum` WHERE `file_posts_id`=? AND `file_id` IN (".$del.");", array($pid));
							}
						}
						// ------ Удаление загруженных файлов -------//


						$_SESSION['note'] = 'Сообщение успешно отредактировано!';
						redirect("forum.php?act=topic&tid=$tid&start=$start");

					} else {
						show_error('Ошибка! Данного сообщения не существует!');
					}
				} else {
					show_error('Ошибка! Слишком длинное или короткое сообщение!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="forum.php?act=editpost&amp;tid='.$tid.'&amp;pid='.$pid.'&amp;start='.$start.'">Вернуться</a><br />';
		break;

	default:
		redirect("forum.php");
	endswitch;

	echo '<img src="/images/img/panel.gif" alt="image" /> <a href="index.php">В админку</a><br />';

} else {
	redirect('/index.php');
}

include_once ('../themes/footer.php');
?>
