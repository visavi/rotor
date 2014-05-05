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
if (isset($_GET['id'])) {
	$id = abs(intval($_GET['id']));
} else {
	$id = 0;
}
if (isset($_GET['cid'])) {
	$cid = abs(intval($_GET['cid']));
} else {
	$cid = 0;
}
if (isset($_GET['start'])) {
	$start = abs(intval($_GET['start']));
} else {
	$start = 0;
}

if (is_admin()) {
	show_title('Управление блогами');

	switch ($act):
	############################################################################################
	##                                    Главная страница                                    ##
	############################################################################################
		case 'index':

			$queryblog = DB::run() -> query("SELECT * FROM `catsblog` ORDER BY `cats_order` ASC;");
			$blogs = $queryblog -> fetchAll();

			if (count($blogs) > 0) {
				foreach($blogs as $data) {
					echo '<img src="/images/img/dir.gif" alt="image" /> ';
					echo '<b>'.$data['cats_order'].'. <a href="blog.php?act=blog&amp;cid='.$data['cats_id'].'">'.$data['cats_name'].'</a></b> ('.$data['cats_count'].')<br />';

					if (is_admin(array(101))) {
						echo '<a href="blog.php?act=editcats&amp;cid='.$data['cats_id'].'">Редактировать</a> / ';
						echo '<a href="blog.php?act=prodelcats&amp;cid='.$data['cats_id'].'">Удалить</a>';
					}
					echo '<br />';
				}
			} else {
				show_error('Разделы блогов еще не созданы!');
			}

			if (is_admin(array(101))) {
				echo '<br /><div class="form">';
				echo '<form action="blog.php?act=addcats&amp;uid='.$_SESSION['token'].'" method="post">';
				echo '<b>Заголовок:</b><br />';
				echo '<input type="text" name="name" maxlength="50" />';
				echo '<input type="submit" value="Создать раздел" /></form></div><br />';

				echo '<img src="/images/img/reload.gif" alt="image" /> <a href="blog.php?act=restatement&amp;uid='.$_SESSION['token'].'">Пересчитать</a><br />';
			}
		break;

		############################################################################################
		##                                    Пересчет счетчиков                                  ##
		############################################################################################
		case 'restatement':

			$uid = check($_GET['uid']);

			if (is_admin(array(101))) {
				if ($uid == $_SESSION['token']) {
					restatement('blog');

					$_SESSION['note'] = 'Все данные успешно пересчитаны!';
					redirect("blog.php");

				} else {
					show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
				}
			} else {
				show_error('Ошибка! Пересчитывать сообщения могут только суперадмины!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="blog.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                                    Добавление разделов                                 ##
		############################################################################################
		case 'addcats':

			$uid = check($_GET['uid']);
			$name = check($_POST['name']);

			if (is_admin(array(101))) {
				if ($uid == $_SESSION['token']) {
					if (utf_strlen($name) >= 3 && utf_strlen($name) < 50) {
						$maxorder = DB::run() -> querySingle("SELECT IFNULL(MAX(`cats_order`),0)+1 FROM `catsblog`;");
						DB::run() -> query("INSERT INTO `catsblog` (`cats_order`, `cats_name`) VALUES (?, ?);", array($maxorder, $name));

						$_SESSION['note'] = 'Новый раздел успешно добавлен!';
						redirect("blog.php");

					} else {
						show_error('Ошибка! Слишком длинное или короткое название раздела!');
					}
				} else {
					show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
				}
			} else {
				show_error('Ошибка! Добавлять разделы могут только суперадмины!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="blog.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                          Подготовка к редактированию разделов                          ##
		############################################################################################
		case 'editcats':

			if (is_admin(array(101))) {
				$blogs = DB::run() -> queryFetch("SELECT * FROM `catsblog` WHERE `cats_id`=? LIMIT 1;", array($cid));

				if (!empty($blogs)) {
					echo '<b><big>Редактирование</big></b><br /><br />';

					echo '<div class="form">';
					echo '<form action="blog.php?act=changecats&amp;cid='.$cid.'&amp;uid='.$_SESSION['token'].'" method="post">';
					echo 'Заголовок:<br />';
					echo '<input type="text" name="name" maxlength="50" value="'.$blogs['cats_name'].'" /><br />';
					echo 'Положение:<br />';
					echo '<input type="text" name="order" maxlength="2" value="'.$blogs['cats_order'].'" /><br /><br />';

					echo '<input type="submit" value="Изменить" /></form></div><br />';
				} else {
					show_error('Ошибка! Данного раздела не существует!');
				}
			} else {
				show_error('Ошибка! Изменять разделы могут только суперадмины!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="blog.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                                 Редактирование разделов                                ##
		############################################################################################
		case 'changecats':

			$uid = check($_GET['uid']);
			$name = check($_POST['name']);
			$order = abs(intval($_POST['order']));

			if (is_admin(array(101))) {
				if ($uid == $_SESSION['token']) {
					if (utf_strlen($name) >= 3 && utf_strlen($name) < 50) {
						$blogs = DB::run() -> queryFetch("SELECT * FROM `catsblog` WHERE `cats_id`=? LIMIT 1;", array($cid));

						if (!empty($blogs)) {
							DB::run() -> query("UPDATE `catsblog` SET `cats_order`=?, `cats_name`=? WHERE `cats_id`=?;", array($order, $name, $cid));

							$_SESSION['note'] = 'Раздел успешно отредактирован!';
							redirect("blog.php");

						} else {
							show_error('Ошибка! Данного раздела не существует!');
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

			echo '<img src="/images/img/reload.gif" alt="image" /> <a href="blog.php?act=editcats&amp;cid='.$cid.'">Вернуться</a><br />';
			echo '<img src="/images/img/back.gif" alt="image" /> <a href="blog.php">Категории</a><br />';
		break;

		############################################################################################
		##                                  Подтвержение удаления                                 ##
		############################################################################################
		case 'prodelcats':

			if (is_admin(array(101))) {
				$blogs = DB::run() -> queryFetch("SELECT * FROM `catsblog` WHERE `cats_id`=? LIMIT 1;", array($cid));

				if (!empty($blogs)) {
					echo 'Вы уверены что хотите удалить раздел <b>'.$blogs['cats_name'].'</b> в блогах?<br />';
					echo '<img src="/images/img/error.gif" alt="image" /> <b><a href="blog.php?act=delcats&amp;cid='.$cid.'&amp;uid='.$_SESSION['token'].'">Да, уверен!</a></b><br /><br />';
				} else {
					show_error('Ошибка! Данного раздела не существует!');
				}
			} else {
				show_error('Ошибка! Удалять разделы могут только суперадмины!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="blog.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                                    Удаление раздела                                    ##
		############################################################################################
		case 'delcats':

			$uid = check($_GET['uid']);

			if (is_admin(array(101)) && $log == $config['nickname']) {
				if ($uid == $_SESSION['token']) {
					$blogs = DB::run() -> queryFetch("SELECT * FROM `catsblog` WHERE `cats_id`=? LIMIT 1;", array($cid));

					if (!empty($blogs)) {
						DB::run() -> query("DELETE FROM `commblog` WHERE `commblog_cats`=?;", array($cid));
						DB::run() -> query("DELETE FROM `blogs` WHERE `blogs_cats_id`=?;", array($cid));
						DB::run() -> query("DELETE FROM `catsblog` WHERE `cats_id`=?;", array($cid));

						$_SESSION['note'] = 'Раздел успешно удален!';
						redirect("blog.php");

					} else {
						show_error('Ошибка! Данного раздела не существует!');
					}
				} else {
					show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
				}
			} else {
				show_error('Ошибка! Удалять разделы могут только суперадмины!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="blog.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                                       Просмотр статей                                  ##
		############################################################################################
		case 'blog':

			$cats = DB::run() -> queryFetch("SELECT * FROM `catsblog` WHERE `cats_id`=? LIMIT 1;", array($cid));

			if (!empty($cats)) {
				$config['newtitle'] = $cats['cats_name'];

				echo '<img src="/images/img/open_dir.gif" alt="image" /> <b>'.$cats['cats_name'].'</b> (Статей: '.$cats['cats_count'].')';
				echo ' (<a href="/blog/blog.php?cid='.$cid.'&amp;start='.$start.'">Обзор</a>)';
				echo '<hr />';

				$total = DB::run() -> querySingle("SELECT count(*) FROM `blogs` WHERE `blogs_cats_id`=?;", array($cid));

				if ($total > 0) {
					if ($start >= $total) {
						$start = 0;
					}

					$queryblog = DB::run() -> query("SELECT * FROM `blogs` WHERE `blogs_cats_id`=? ORDER BY `blogs_time` DESC LIMIT ".$start.", ".$config['blogpost'].";", array($cid));

					echo '<form action="blog.php?act=delblog&amp;cid='.$cid.'&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';

					while ($data = $queryblog -> fetch()) {

						echo '<div class="b"><img src="/images/img/edit.gif" alt="image" /> ';
						echo '<b><a href="/blog/blog.php?act=view&amp;id='.$data['blogs_id'].'">'.$data['blogs_title'].'</a></b> ('.format_num($data['blogs_rating']).')<br />';

						echo '<input type="checkbox" name="del[]" value="'.$data['blogs_id'].'" /> ';

						echo '<a href="blog.php?act=editblog&amp;cid='.$cid.'&amp;id='.$data['blogs_id'].'&amp;start='.$start.'">Редактировать</a> / ';
						echo '<a href="blog.php?act=moveblog&amp;cid='.$cid.'&amp;id='.$data['blogs_id'].'&amp;start='.$start.'">Переместить</a></div>';

						echo '<div>Автор: '.profile($data['blogs_user']).' ('.date_fixed($data['blogs_time']).')<br />';
						echo 'Просмотров: '.$data['blogs_read'].'<br />';
						echo '<a href="/blog/blog.php?act=comments&amp;id='.$data['blogs_id'].'">Комментарии</a> ('.$data['blogs_comments'].')<br />';
						echo '</div>';
					}

					echo '<br /><input type="submit" value="Удалить выбранное" /></form>';

					page_strnavigation('blog.php?act=blog&amp;cid='.$cid.'&amp;', $config['blogpost'], $start, $total);
				} else {
					show_error('В данном разделе еще нет статей!');
				}
			} else {
				show_error('Ошибка! Данного раздела не существует!');
			}

			echo '<img src="/images/img/reload.gif" alt="image" /> <a href="blog.php">Категории</a><br />';
		break;

		############################################################################################
		##                            Подготовка к редактированию статьи                          ##
		############################################################################################
		case 'editblog':

			$blogs = DB::run() -> queryFetch("SELECT * FROM `blogs` WHERE `blogs_id`=? LIMIT 1;", array($id));

			if (!empty($blogs)) {
				$blogs['blogs_text'] = nosmiles($blogs['blogs_text']);
				$blogs['blogs_text'] = yes_br($blogs['blogs_text']);

				echo '<b><big>Редактирование</big></b><br /><br />';

				echo '<div class="form">';
				echo '<form action="blog.php?act=addeditblog&amp;cid='.$cid.'&amp;id='.$id.'&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';

				echo 'Заголовок:<br />';
				echo '<input type="text" name="title" size="50" maxlength="50" value="'.$blogs['blogs_title'].'" /><br />';
				echo 'Текст:<br />';
				echo '<textarea id="markItUp" cols="25" rows="15" name="text">'.$blogs['blogs_text'].'</textarea><br />';
				echo 'Автор:<br />';
				echo '<input type="text" name="user" maxlength="20" value="'.$blogs['blogs_user'].'" /><br />';
				echo 'Метки:<br />';
				echo '<input type="text" name="tags" size="50" maxlength="100" value="'.$blogs['blogs_tags'].'" /><br />';

				echo '<input type="submit" value="Изменить" /></form></div><br />';
			} else {
				show_error('Ошибка! Данной статьи не существует!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="blog.php?act=blog&amp;cid='.$cid.'&amp;start='.$start.'">Вернуться</a><br />';
			echo '<img src="/images/img/reload.gif" alt="image" /> <a href="blog.php">Категории</a><br />';
		break;

		############################################################################################
		##                                  Редактирование статьи                                ##
		############################################################################################
		case 'addeditblog':

			$uid = check($_GET['uid']);
			$title = check($_POST['title']);
			$text = check($_POST['text']);
			$user = check($_POST['user']);
			$tags = check($_POST['tags']);

			if ($uid == $_SESSION['token']) {
				if (utf_strlen($title) >= 5 && utf_strlen($title) <= 50) {
					if (utf_strlen($text) >= 100 && utf_strlen($text) <= $config['maxblogpost']) {
						if (utf_strlen($tags) >= 2 && utf_strlen($tags) <= 50) {
							if (preg_match('|^[a-z0-9\-]+$|i', $user)) {
								$queryblog = DB::run() -> querySingle("SELECT `blogs_id` FROM `blogs` WHERE `blogs_id`=? LIMIT 1;", array($id));
								if (!empty($queryblog)) {
									$text = no_br($text);
									$text = smiles($text);

									DB::run() -> query("UPDATE `blogs` SET `blogs_title`=?, `blogs_text`=?, `blogs_user`=?, `blogs_tags`=? WHERE `blogs_id`=?;", array($title, $text, $user, $tags, $id));

									$_SESSION['note'] = 'Статья успешно отредактирована!';
									redirect("blog.php?act=blog&cid=$cid&start=$start");

								} else {
									show_error('Ошибка! Данной статьи не существует!');
								}
							} else {
								show_error('Ошибка! Недопустимые символы в логине! Разрешены только знаки латинского алфавита и цифры!');
							}
						} else {
							show_error('Ошибка! Слишком длинные или короткие метки статьи (от 2 до 50 символов)!');
						}
					} else {
						show_error('Ошибка! Слишком длинный или короткий текст статьи (от 100 до '.$config['maxblogpost'].' символов)!');
					}
				} else {
					show_error('Ошибка! Слишком длинный или короткий заголовок (от 5 до 50 символов)!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="blog.php?act=editblog&amp;id='.$id.'&amp;start='.$start.'">Вернуться</a><br />';
			echo '<img src="/images/img/reload.gif" alt="image" /> <a href="blog.php?act=blog&amp;cid='.$cid.'&amp;start='.$start.'">В раздел</a><br />';
		break;

		############################################################################################
		##                               Подготовка к перемещению статьи                          ##
		############################################################################################
		case 'moveblog':

			$blogs = DB::run() -> queryFetch("SELECT * FROM `blogs` WHERE `blogs_id`=? LIMIT 1;", array($id));

			if (!empty($blogs)) {
				echo '<img src="/images/img/zip.gif" alt="image" /> <b>'.$blogs['blogs_title'].'</b><br /><br />';

				$querycats = DB::run() -> query("SELECT `cats_id`, `cats_name` FROM `catsblog` ORDER BY `cats_order` ASC;");
				$cats = $querycats -> fetchAll();

				if (count($cats) > 0) {
					echo '<div class="form">';
					echo '<form action="blog.php?act=addmoveblog&amp;cid='.$blogs['blogs_cats_id'].'&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'" method="post">';

					echo 'Выберите раздел для перемещения:<br />';
					echo '<select name="section">';
					echo '<option value="0">Список разделов</option>';

					foreach ($cats as $data) {
						if ($blogs['blogs_cats_id'] != $data['cats_id']) {
							echo '<option value="'.$data['cats_id'].'">'.$data['cats_name'].'</option>';
						}
					}

					echo '</select>';
					echo '<input type="submit" value="Переместить" /></form></div><br />';
				} else {
					show_error('Ошибка! Разделы блогов еще не созданы!');
				}
			} else {
				show_error('Ошибка! Данной статьи не существует!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="blog.php?act=blog&amp;cid='.$cid.'&amp;start='.$start.'">Вернуться</a><br />';
		break;

		############################################################################################
		##                                    Перемещение статьи                                  ##
		############################################################################################
		case 'addmoveblog':

			$uid = check($_GET['uid']);
			$section = abs(intval($_POST['section']));

			if ($uid == $_SESSION['token']) {
				$querycats = DB::run() -> querySingle("SELECT `cats_id` FROM `catsblog` WHERE `cats_id`=? LIMIT 1;", array($section));
				if (!empty($querycats)) {
					$queryblog = DB::run() -> querySingle("SELECT `blogs_id` FROM `blogs` WHERE `blogs_id`=? LIMIT 1;", array($id));
					if (!empty($queryblog)) {
						DB::run() -> query("UPDATE `blogs` SET `blogs_cats_id`=? WHERE `blogs_id`=?;", array($section, $id));
						DB::run() -> query("UPDATE `commblog` SET `commblog_cats`=? WHERE `commblog_blog`=?;", array($section, $id));
						// Обновление счетчиков
						DB::run() -> query("UPDATE `catsblog` SET `cats_count`=`cats_count`+1 WHERE `cats_id`=?", array($section));
						DB::run() -> query("UPDATE `catsblog` SET `cats_count`=`cats_count`-1 WHERE `cats_id`=?", array($cid));

						$_SESSION['note'] = 'Статья успешно перемещена!';
						redirect("blog.php?act=blog&cid=$section");

					} else {
						show_error('Ошибка! Статьи для перемещения не существует!');
					}
				} else {
					show_error('Ошибка! Выбранного раздела не существует!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="blog.php?act=moveblog&amp;cid='.$cid.'&amp;id='.$id.'">Вернуться</a><br />';
			echo '<img src="/images/img/reload.gif" alt="image" /> <a href="blog.php?act=blog&amp;cid='.$cid.'">К блогам</a><br />';
		break;

		############################################################################################
		##                                     Удаление статей                                    ##
		############################################################################################
		case 'delblog':

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
					$del = implode(',', $del);

					DB::run() -> query("DELETE FROM `commblog` WHERE `commblog_blog` IN (".$del.");");
					$delblogs = DB::run() -> exec("DELETE FROM `blogs` WHERE `blogs_id` IN (".$del.");");
					// Обновление счетчиков
					DB::run() -> query("UPDATE `catsblog` SET `cats_count`=`cats_count`-? WHERE `cats_id`=?", array($delblogs, $cid));

					$_SESSION['note'] = 'Выбранные статьи успешно удалены!';
					redirect("blog.php?act=blog&cid=$cid&start=$start");

				} else {
					show_error('Ошибка! Отсутствуют выбранные статьи для удаления!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="blog.php?act=blog&amp;cid='.$cid.'&amp;start='.$start.'">Вернуться</a><br />';
		break;

	default:
		redirect("blog.php");
	endswitch;

	echo '<img src="/images/img/panel.gif" alt="image" /> <a href="index.php">В админку</a><br />';

} else {
	redirect('/index.php');
}

include_once ('../themes/footer.php');
?>
