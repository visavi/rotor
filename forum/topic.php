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
$tid = (isset($_GET['tid'])) ? abs(intval($_GET['tid'])) : 0;
$start = (isset($_GET['start'])) ? abs(intval($_GET['start'])) : 0;

show_title('Форум '.$config['title']);

switch ($act):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
case 'index':

	if (!empty($tid)) {
		$topics = DB::run() -> queryFetch("SELECT `topics`.*, `forums`.`forums_id`, `forums`.`forums_title`, `forums`.`forums_parent` FROM `topics` LEFT JOIN `forums` ON `topics`.`topics_forums_id`=`forums`.`forums_id` WHERE `topics_id`=? LIMIT 1;", array($tid));

		if (!empty($topics)) {
			$config['header'] = $topics['topics_title'];

			if (!empty($topics['forums_parent'])) {
				$topics['subparent'] = DB::run() -> queryFetch("SELECT `forums_id`, `forums_title` FROM `forums` WHERE `forums_id`=? LIMIT 1;", array($topics['forums_parent']));
			}

			if (is_user()) {
				$topics['bookmark'] = DB::run() -> queryFetch("SELECT * FROM `bookmarks` WHERE `book_topic`=? AND `book_user`=? LIMIT 1;", array($tid, $log));

				if (!empty($topics['bookmark']) && $topics['topics_posts'] > $topics['bookmark']['book_posts']) {
					DB::run() -> query("UPDATE `bookmarks` SET `book_posts`=? WHERE `book_topic`=? AND `book_user`=? LIMIT 1;", array($topics['topics_posts'], $tid, $log));
				}
			}

			// --------------------------------------------------------------//
			if (!empty($topics['topics_mod'])) {
				$topics['curator'] = explode(',', $topics['topics_mod']);
				$topics['is_moder'] = (in_array($log, $topics['curator'])) ? 1 : 0;
			}

			$total = DB::run() -> querySingle("SELECT count(*) FROM `posts` WHERE `posts_topics_id`=?;", array($tid));

			if ($total > 0 && $start >= $total) {
				$start = last_page($total, $config['forumpost']);
			}

			$page = floor(1 + $start / $config['forumpost']);
			$config['newtitle'] = $topics['topics_title'].' (Стр. '.$page.')';
			$config['description'] = 'Обсуждение темы: '.$topics['topics_title'].' (Стр. '.$page.')';


			$querypost = DB::run() -> query("SELECT * FROM `posts` WHERE `posts_topics_id`=? ORDER BY `posts_time` ASC LIMIT ".$start.", ".$config['forumpost'].";", array($tid));

			$topics['posts'] = $querypost->fetchAll();

			// ----- Получение массива файлов ----- //
			$ipdpost = array();
			foreach ($topics['posts'] as $val) {
				$ipdpost[] = $val['posts_id'];
			}

			$ipdpost = implode(',', $ipdpost);
			
			if (!empty($ipdpost)) {
				$queryfiles = DB::run() -> query("SELECT * FROM `files_forum` WHERE `file_posts_id` IN (".$ipdpost.");");
				$files = $queryfiles->fetchAll();
			}
			if (!empty($files)){
				$forumfiles = array();
				foreach ($files as $file){
					$topics['posts_files'][$file['file_posts_id']][] = $file;
				}
			}
			// ------------------------------------- //

			render('forum/topic', array('topics' => $topics, 'tid' => $tid, 'start' => $start, 'total' => $total));

		} else {
			show_error('Ошибка! Данной темы не существует!');
		}

	} else {
		redirect("index.php");
	}
break;

############################################################################################
##                                   Добавление файла                                     ##
############################################################################################
case 'addfile':
	if (is_user()) {
		if ($udata['users_point'] >= $config['forumloadpoints']){

			render('forum/topic_addfile', array('tid' => $tid, 'start' => $start));

		} else {
			show_error('Ошибка! У вас недостаточно актива для загрузки файлов!');
		}
	} else {
		show_login('Вы не авторизованы, чтобы добавить сообщение, необходимо');
	}

	render('includes/back', array('link' => 'topic.php?tid='.$tid.'&amp;start='.$start, 'title' => 'Вернуться'));
break;

############################################################################################
##                                   Добавление сообщения                                 ##
############################################################################################
case 'add':

	$uid = (!empty($_GET['uid'])) ? check($_GET['uid']) : 0;
	$msg = (isset($_POST['msg'])) ? check($_POST['msg']) : '';

	if (is_user()) {

	$topics = DB::run() -> queryFetch("SELECT `topics`.*, `forums`.`forums_parent` FROM `topics` LEFT JOIN `forums` ON `topics`.`topics_forums_id`=`forums`.`forums_id` WHERE `topics`.`topics_id`=? LIMIT 1;", array($tid));

		$validation = new Validation;

		$validation -> addRule('equal', array($uid, $_SESSION['token']), 'Неверный идентификатор сессии, повторите действие!')
			-> addRule('not_empty', $topics, 'Выбранная вами тема не существует, возможно она была удалена!')
			-> addRule('empty', $topics['topics_closed'], 'Запрещено писать в закрытую тему!')
			-> addRule('equal', array(is_quarantine($log), true), 'Карантин! Вы не можете писать в течении '.round($config['karantin'] / 3600).' часов!')
			-> addRule('equal', array(is_flood($log), true), 'Антифлуд! Разрешается отправлять сообщения раз в '.flood_period().' сек!')
			-> addRule('string', $msg, 'Слишком длинное или короткое сообщение!', true, 5, $config['forumtextlength']);

			// Проверка сообщения на схожесть
			$post = DB::run() -> queryFetch("SELECT * FROM `posts` WHERE `posts_topics_id`=? ORDER BY `posts_id` DESC LIMIT 1;", array($tid));
			$validation -> addRule('not_equal', array($msg, $post['posts_text']), 'Ваше сообщение повторяет предыдущий пост!');

		if ($validation->run(1)) {

			$msg = smiles(antimat(no_br($msg)));

			if ($log == $post['posts_user'] && $post['posts_time'] + 600 > SITETIME && (utf_strlen($msg) + utf_strlen($post['posts_text']) <= $config['forumtextlength'])) {
				$newpost = $post['posts_text'].'<br /><br />[i][small]Добавлено через '.maketime(SITETIME - $post['posts_time']).' сек.[/small][/i]<br />'.$msg;

				DB::run() -> query("UPDATE `posts` SET `posts_text`=? WHERE `posts_id`=? LIMIT 1;", array($newpost, $post['posts_id']));
				$lastid = $post['posts_id'];

			} else {

				DB::run() -> query("INSERT INTO `posts` (`posts_topics_id`, `posts_forums_id`, `posts_user`, `posts_text`, `posts_time`, `posts_ip`, `posts_brow`) VALUES (?, ?, ?, ?, ?, ?, ?);", array($tid, $topics['topics_forums_id'], $log, $msg, SITETIME, $ip, $brow));
				$lastid = DB::run() -> lastInsertId();

				DB::run() -> query("UPDATE `users` SET `users_allforum`=`users_allforum`+1, `users_point`=`users_point`+1, `users_money`=`users_money`+5 WHERE `users_login`=? LIMIT 1;", array($log));

				DB::run() -> query("UPDATE `topics` SET `topics_posts`=`topics_posts`+1, `topics_last_user`=?, `topics_last_time`=? WHERE `topics_id`=?;", array($log, SITETIME, $tid));

				DB::run() -> query("UPDATE `forums` SET `forums_posts`=`forums_posts`+1, `forums_last_id`=?, `forums_last_themes`=?, `forums_last_user`=?, `forums_last_time`=? WHERE `forums_id`=?;", array($tid, $topics['topics_title'], $log, SITETIME, $topics['topics_forums_id']));
				// Обновление родительского форума
				if ($topics['forums_parent'] > 0) {
					DB::run() -> query("UPDATE `forums` SET `forums_last_id`=?, `forums_last_themes`=?, `forums_last_user`=?, `forums_last_time`=? WHERE `forums_id`=?;", array($tid, $topics['topics_title'], $log, SITETIME, $topics['forums_parent']));
				}
			}

			// -- Загрузка файла -- //
			if (!empty($_FILES['file']['name']) && !empty($lastid)) {
				if ($udata['users_point'] >= $config['forumloadpoints']){
					if (is_uploaded_file($_FILES['file']['tmp_name'])) {

						$filename = check($_FILES['file']['name']);
						$filename = (!is_utf($filename)) ? utf_lower(win_to_utf($filename)) : utf_lower($filename);
						$filesize = $_FILES['file']['size'];

						if ($filesize > 0 && $filesize <= $config['forumloadsize']) {
							$arrext = explode(',', $config['forumextload']);
							$ext = getExtension($filename);

							if (in_array($ext, $arrext) && $ext != 'php') {

								if (utf_strlen($filename) > 50) {
									$filename = utf_substr($filename, 0, 45).'.'.$ext;
								}

								if (!file_exists(BASEDIR.'/upload/forum/'.$topics['topics_id'])){
									$old = umask(0);
									mkdir(BASEDIR.'/upload/forum/'.$topics['topics_id'], 0777, true);
									umask($old);
								}

								$num = 0;
								$hash = $lastid.'.'.$ext;
								while(file_exists(BASEDIR.'/upload/forum/'.$topics['topics_id'].'/'.$hash)){
									$num++;
									$hash = $lastid.'_'.$num.'.'.$ext;
								}

								move_uploaded_file($_FILES['file']['tmp_name'], BASEDIR.'/upload/forum/'.$topics['topics_id'].'/'.$hash);

								DB::run() -> query("INSERT INTO `files_forum` (`file_topics_id`, `file_posts_id`, `file_hash`, `file_name`, `file_size`, `file_user`, `file_time`) VALUES (?, ?, ?, ?, ?, ?, ?);", array($topics['topics_id'], $lastid, $hash, $filename, $filesize, $log, SITETIME));

							} else {
								notice('Файл не загружен! Недопустимое расширение!', '#ff0000');
							}
						} else {
							notice('Файл не загружен! Максимальный размер '.formatsize($config['forumloadsize']).'!', '#ff0000');
						}
					} else {
						notice('Ошибка! Не удалось загрузить файл!', '#ff0000');
					}
				} else {
					notice('Ошибка! У вас недостаточно актива для загрузки файлов!', '#ff0000');
				}
			}
			// -- Загрузка файла -- //

			notice('Сообщение успешно добавлено!');
			redirect("topic.php?act=end&tid=$tid");
		} else {
			show_error($validation->errors);
		}
	} else {
		show_login('Вы не авторизованы, чтобы добавить сообщение, необходимо');
	}

	render('includes/back', array('link' => 'topic.php?tid='.$tid.'&amp;start='.$start, 'title' => 'Вернуться'));
break;

############################################################################################
##                                    Жалоба на спам                                      ##
############################################################################################
case 'spam':

	$uid = check($_GET['uid']);
	$pid = abs(intval($_GET['pid']));

	if (is_user()) {
		if ($uid == $_SESSION['token']) {
			$data = DB::run() -> queryFetch("SELECT * FROM `posts` WHERE `posts_id`=? LIMIT 1;", array($pid));
			if (!empty($data)) {
				$queryspam = DB::run() -> querySingle("SELECT `spam_id` FROM `spam` WHERE `spam_key`=? AND `spam_idnum`=? LIMIT 1;", array(1, $pid));

				if (empty($queryspam)) {
					if (is_flood($log)) {
						DB::run() -> query("INSERT INTO `spam` (`spam_key`, `spam_idnum`, `spam_user`, `spam_login`, `spam_text`, `spam_time`, `spam_addtime`, `spam_link`) VALUES (?, ?, ?, ?, ?, ?, ?, ?);", array(1, $data['posts_id'], $log, $data['posts_user'], $data['posts_text'], $data['posts_time'], SITETIME, $config['home'].'/forum/topic.php?tid='.$tid.'&amp;start='.$start));

						$_SESSION['note'] = 'Жалоба успешно отправлена!';
						redirect("topic.php?tid=$tid&start=$start");

					} else {
						show_error('Антифлуд! Разрешается жаловаться на спам не чаще чем раз в '.flood_period().' секунд!');
					}
				} else {
					show_error('Ошибка! Жалоба на данное сообщение уже отправлена!');
				}
			} else {
				show_error('Ошибка! Выбранное вами сообщение для жалобы не существует!');
			}
		} else {
			show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
		}
	} else {
		show_login('Вы не авторизованы, чтобы подать жалобу, необходимо');
	}

	render('includes/back', array('link' => 'topic.php?tid='.$tid.'&amp;start='.$start, 'title' => 'Вернуться'));
break;

############################################################################################
##                                    Удаление сообщений                                  ##
############################################################################################
case 'del':
	$uid = check($_GET['uid']);
	$del = (isset($_POST['del'])) ? intar($_POST['del']) : 0;

	if (is_user()) {
		if ($uid == $_SESSION['token']) {
			if (!empty($del)) {
				$topics = DB::run() -> queryFetch("SELECT * FROM `topics` WHERE `topics_id`=? LIMIT 1;", array($tid));
				$minposts = DB::run() -> querySingle("SELECT min(`posts_id`) FROM `posts` WHERE `posts_topics_id`=?;", array($tid));

				if (!empty($topics['topics_mod'])) {
					$topics_mod = explode(',', $topics['topics_mod']);
					if (in_array($log, $topics_mod)) {
						if (!in_array($minposts, $del)) {
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
							redirect("topic.php?tid=$tid&start=$start");

						} else {
							show_error('Ошибка! Первое сообщение в теме удалять запрещено!');
						}
					} else {
						show_error('Ошибка! Удалять сообщения могут только кураторы темы!');
					}
				} else {
					show_error('Ошибка! В данной теме отсутствуют кураторы!');
				}
			} else {
				show_error('Ошибка! Отстутствуют выбранные сообщения для удаления!');
			}
		} else {
			show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
		}
	} else {
		show_login('Вы не авторизованы, чтобы добавить сообщение, необходимо');
	}

	render('includes/back', array('link' => 'topic.php?tid='.$tid.'&amp;start='.$start, 'title' => 'Вернуться'));
break;

############################################################################################
##                                       Закрытие темы                                    ##
############################################################################################
case 'closed':

	$uid = check($_GET['uid']);

	if ($uid == $_SESSION['token']) {
		if (is_user()) {
			if ($udata['users_point'] >= $config['editforumpoint']) {
				$topics = DB::run() -> queryFetch("SELECT * FROM `topics` WHERE `topics_id`=? LIMIT 1;", array($tid));

				if (!empty($topics)) {
					if ($topics['topics_author'] == $log) {
						if (empty($topics['topics_closed'])) {
							DB::run() -> query("UPDATE `topics` SET `topics_closed`=? WHERE `topics_id`=?;", array(1, $tid));

							$_SESSION['note'] = 'Тема успешно закрыта!';
							redirect("topic.php?tid=$tid&start=$start");

						} else {
							show_error('Ошибка! Данная тема уже закрыта!');
						}
					} else {
						show_error('Ошибка! Вы не автор данной темы!');
					}
				} else {
					show_error('Ошибка! Выбранная вами тема не существует, возможно она была удалена!');
				}
			} else {
				show_error('Ошибка! У вас недостаточно актива для закрытия тем!');
			}
		} else {
			show_login('Вы не авторизованы, чтобы закрывать темы, необходимо');
		}
	} else {
		show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
	}

	render('includes/back', array('link' => 'topic.php?tid='.$tid.'&amp;start='.$start, 'title' => 'Вернуться'));
break;

############################################################################################
##                                   Ответ на сообщение                                   ##
############################################################################################
case 'reply':

	show_title('Ответ на сообщение');

	$pid = (isset($_GET['pid'])) ? abs(intval($_GET['pid'])) : 0;
	$num = (isset($_GET['num'])) ? abs(intval($_GET['num'])) : 0;

	if (is_user()) {
		$post = DB::run() -> queryFetch("SELECT `posts`.*, `topics`.`topics_closed` FROM `posts` LEFT JOIN `topics` ON `posts`.`posts_topics_id`=`topics`.`topics_id` WHERE `posts_id`=? LIMIT 1;", array($pid));

		if (!empty($post)) {
			if (empty($post['topics_closed'])) {

				render('forum/topic_reply', array('post' => $post, 'start' => $start, 'num' => $num));

			} else {
				show_error('Данная тема закрыта для обсуждения!');
			}
		} else {
			show_error('Ошибка! Выбранное вами сообщение для ответа не существует!');
		}
	} else {
		show_login('Вы не авторизованы, чтобы отвечать на сообщения, необходимо');
	}

	render('includes/back', array('link' => 'topic.php?tid='.$tid.'&amp;start='.$start, 'title' => 'Вернуться'));
break;

############################################################################################
##                                   Цитирование сообщения                                ##
############################################################################################
case 'quote':

	$pid = (isset($_GET['pid'])) ? abs(intval($_GET['pid'])) : 0;


	if (is_user()) {
		$post = DB::run() -> queryFetch("SELECT `posts`.*, `topics`.`topics_closed` FROM `posts` LEFT JOIN `topics` ON `posts`.`posts_topics_id`=`topics`.`topics_id` WHERE `posts_id`=? LIMIT 1;", array($pid));

		if (!empty($post)) {
			if (empty($post['topics_closed'])) {

				$post['posts_text'] = nosmiles($post['posts_text']);
				$post['posts_text'] = preg_replace('|\[q\](.*?)\[/q\](<br />)?|', '', $post['posts_text']);
				$post['posts_text'] = yes_br($post['posts_text']);

				render('forum/topic_quote', array('post' => $post, 'start' => $start));

			} else {
				show_error('Данная тема закрыта для обсуждения!');
			}
		} else {
			show_error('Ошибка! Выбранное вами сообщение для цитирования не существует!');
		}
	} else {
		show_login('Вы не авторизованы, чтобы цитировать сообщения, необходимо');
	}

	render('includes/back', array('link' => 'topic.php?tid='.$tid.'&amp;start='.$start, 'title' => 'Вернуться'));
break;

############################################################################################
##                                   Подготовка к изменению                               ##
############################################################################################
case 'edittopic':

	if (is_user()) {
		if ($udata['users_point'] >= $config['editforumpoint']) {
			$topics = DB::run() -> queryFetch("SELECT * FROM `topics` WHERE `topics_id`=? LIMIT 1;", array($tid));

			if (!empty($topics)) {
				if ($topics['topics_author'] == $log) {
					if (empty($topics['topics_closed'])) {
						$post = DB::run() -> queryFetch("SELECT * FROM `posts` WHERE `posts_topics_id`=? ORDER BY posts_id ASC LIMIT 1;", array($tid));
						if (!empty($post)) {

							$post['posts_text'] = yes_br(nosmiles($post['posts_text']));
							render('forum/topic_edittopic', array('post' => $post, 'topics' => $topics));

						} else {
							show_error('Ошибка! Первого сообщения в теме не существует!');
						}
					} else {
						show_error('Ошибка! Изменение невозможно, данная тема закрыта!');
					}
				} else {
					show_error('Ошибка! Изменение невозможно, вы не автор данной темы!');
				}
			} else {
				show_error('Ошибка! Выбранная вами тема не существует, возможно она была удалена!');
			}
		} else {
			show_error('Ошибка! У вас недостаточно актива для изменения темы!');
		}
	} else {
		show_login('Вы не авторизованы, чтобы изменять темы, необходимо');
	}

	render('includes/back', array('link' => 'topic.php?tid='.$tid, 'title' => 'Вернуться'));
break;

############################################################################################
##                                  Изменение темы и сообщения                            ##
############################################################################################
case 'changetopic':

	$uid = check($_GET['uid']);
	$title = check($_POST['title']);
	$msg = check($_POST['msg']);

	if (is_user()) {
		if ($uid == $_SESSION['token']) {
			if ($udata['users_point'] >= $config['editforumpoint']) {
				$topics = DB::run() -> queryFetch("SELECT * FROM `topics` WHERE `topics_id`=? LIMIT 1;", array($tid));

				if (!empty($topics)) {
					if ($topics['topics_author'] == $log) {
						if (empty($topics['topics_closed'])) {
							$minposts = DB::run() -> querySingle("SELECT MIN(`posts_id`) FROM `posts` WHERE `posts_topics_id`=?;", array($tid));
							if (!empty($minposts)) {
								if (utf_strlen($title) >= 5 && utf_strlen($title) <= 50) {
									if (utf_strlen($msg) >= 5 && utf_strlen($msg) <= $config['forumtextlength']) {
										$title = antimat($title);
										$msg = no_br($msg);
										$msg = antimat($msg);
										$msg = smiles($msg);

										DB::run() -> query("UPDATE `topics` SET `topics_title`=? WHERE topics_id=?;", array($title, $tid));
										DB::run() -> query("UPDATE `posts` SET `posts_user`=?, `posts_text`=?, `posts_ip`=?, `posts_brow`=?, `posts_edit`=?, `posts_edit_time`=? WHERE `posts_id`=?;", array($log, $msg, $ip, $brow, $log, SITETIME, $minposts));

										$_SESSION['note'] = 'Тема успешно изменена!';
										redirect("topic.php?tid=$tid");

									} else {
										show_error('Ошибка! Слишком длинное или короткое сообщение!');
									}
								} else {
									show_error('Ошибка! Слишком длинный или короткий заголовок темы!');
								}
							} else {
								show_error('Ошибка! Первого сообщения в теме не существует!');
							}
						} else {
							show_error('Ошибка! Изменение невозможно, данная тема закрыта!');
						}
					} else {
						show_error('Ошибка! Изменение невозможно, вы не автор данной темы!');
					}
				} else {
					show_error('Ошибка! Выбранная вами тема не существует, возможно она была удалена!');
				}
			} else {
				show_error('Ошибка! У вас недостаточно актива для изменения темы!');
			}
		} else {
			show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
		}
	} else {
		show_login('Вы не авторизованы, чтобы редактировать сообщения, необходимо');
	}

	render('includes/back', array('link' => 'topic.php?act=edittopic&amp;tid='.$tid, 'title' => 'Вернуться'));
break;

############################################################################################
##                          Подготовка к редактированию куратором                         ##
############################################################################################
case 'modedit':
	$pid = abs(intval($_GET['pid']));

	if (is_user()) {
		$post = DB::run() -> queryFetch("SELECT `posts`.*, `topics`.`topics_closed`, `topics`.`topics_mod` FROM `posts` LEFT JOIN `topics` ON `posts`.`posts_topics_id`=`topics`.`topics_id` WHERE `posts_id`=? LIMIT 1;", array($pid));

		if (!empty($post)) {
			if (empty($post['topics_closed'])) {
				if (!empty($post['topics_mod'])) {
					$topic_mod = explode(',', $post['topics_mod']);
					if (in_array($log, $topic_mod)) {

						$post['posts_text'] = yes_br(nosmiles($post['posts_text']));
						render('forum/topic_modedit', array('post' => $post, 'pid' => $pid, 'start' => $start));

					} else {
						show_error('Ошибка! Редактировать сообщения могут только кураторы темы!');
					}
				} else {
					show_error('Ошибка! В данной теме отсутствуют кураторы!');
				}
			} else {
				show_error('Ошибка! Редактирование невозможно, данная тема закрыта!');
			}
		} else {
			show_error('Ошибка! Данного сообщения не существует!');
		}
	} else {
		show_login('Вы не авторизованы, чтобы редактировать сообщения, необходимо');
	}

	render('includes/back', array('link' => 'topic.php?tid='.$tid.'&amp;start='.$start, 'title' => 'Вернуться'));
break;

############################################################################################
##                                    Редактирование сообщения                            ##
############################################################################################
case 'modeditpost':

	$uid = check($_GET['uid']);
	$pid = abs(intval($_GET['pid']));
	$msg = check($_POST['msg']);

	if (is_user()) {
		if ($uid == $_SESSION['token']) {
			if (utf_strlen($msg) >= 5 && utf_strlen($msg) <= $config['forumtextlength']) {
				$post = DB::run() -> queryFetch("SELECT `posts`.*, `topics`.`topics_closed`, `topics`.`topics_mod` FROM `posts` LEFT JOIN `topics` ON `posts`.`posts_topics_id`=`topics`.`topics_id` WHERE `posts_id`=? LIMIT 1;", array($pid));

				if (!empty($post)) {
					if (empty($post['topics_closed'])) {
						if (!empty($post['topics_mod'])) {
							$topic_mod = explode(',', $post['topics_mod']);
							if (in_array($log, $topic_mod)) {

								$msg = no_br($msg);
								$msg = antimat($msg);
								$msg = smiles($msg);

								DB::run() -> query("UPDATE `posts` SET `posts_text`=?, `posts_edit`=?, `posts_edit_time`=? WHERE `posts_id`=?;", array($msg, $log, SITETIME, $pid));

								$_SESSION['note'] = 'Сообщение успешно отредактировано!';
								redirect("topic.php?tid=$tid&start=$start");

							} else {
								show_error('Ошибка! Редактировать сообщения могут только кураторы темы!');
							}
						} else {
							show_error('Ошибка! В данной теме отсутствуют кураторы!');
						}
					} else {
						show_error('Ошибка! Редактирование невозможно, данная тема закрыта!');
					}
				} else {
					show_error('Ошибка! Сообщение удалено или вы не автор этого сообщения!');
				}
			} else {
				show_error('Ошибка! Слишком длинное или короткое сообщение!');
			}
		} else {
			show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
		}
	} else {
		show_login('Вы не авторизованы, чтобы редактировать сообщения, необходимо');
	}

	render('includes/back', array('link' => 'topic.php?act=modedit&amp;tid='.$tid.'&amp;pid='.$pid.'&amp;start='.$start, 'title' => 'Вернуться'));
break;

############################################################################################
##                                Подготовка к редактированию                             ##
############################################################################################
case 'edit':

	$pid = abs(intval($_GET['pid']));

	if (is_user()) {
		$post = DB::run() -> queryFetch("SELECT `posts`.*, `topics`.`topics_closed` FROM `posts` LEFT JOIN `topics` ON `posts`.`posts_topics_id`=`topics`.`topics_id` WHERE `posts_id`=? AND `posts_user`=? LIMIT 1;", array($pid, $log));

		if (!empty($post)) {
			if (empty($post['topics_closed'])) {
				if ($post['posts_time'] + 600 > SITETIME) {

					$post['posts_text'] = yes_br(nosmiles($post['posts_text']));

					$queryfiles = DB::run() -> query("SELECT * FROM `files_forum` WHERE `file_posts_id`=?;", array($pid));
					$files = $queryfiles->fetchAll();

					render('forum/topic_edit', array('post' => $post, 'files' => $files, 'pid' => $pid, 'start' => $start));

				} else {
					show_error('Ошибка! Редактирование невозможно, прошло более 10 минут!!');
				}
			} else {
				show_error('Ошибка! Редактирование невозможно, данная тема закрыта!');
			}
		} else {
			show_error('Ошибка! Сообщение удалено или вы не автор этого сообщения!');
		}
	} else {
		show_login('Вы не авторизованы, чтобы редактировать сообщения, необходимо');
	}

	render('includes/back', array('link' => 'topic.php?tid='.$tid.'&amp;start='.$start, 'title' => 'Вернуться'));
break;

############################################################################################
##                                    Редактирование сообщения                            ##
############################################################################################
case 'editpost':

	$uid = check($_GET['uid']);
	$pid = abs(intval($_GET['pid']));
	$msg = check($_POST['msg']);

	if (isset($_POST['delfile'])) {
		$del = intar($_POST['delfile']);
	} else {
		$del = 0;
	}

	if (is_user()) {
		if ($uid == $_SESSION['token']) {
			if (utf_strlen($msg) >= 5 && utf_strlen($msg) <= $config['forumtextlength']) {
				$post = DB::run() -> queryFetch("SELECT `posts`.*, `topics`.`topics_closed` FROM `posts` LEFT JOIN `topics` ON `posts`.`posts_topics_id`=`topics`.`topics_id` WHERE `posts_id`=? AND `posts_user`=? LIMIT 1;", array($pid, $log));

				if (!empty($post)) {
					if (empty($post['topics_closed'])) {
						if ($post['posts_time'] + 600 > SITETIME) {

							$msg = no_br($msg);
							$msg = antimat($msg);
							$msg = smiles($msg);

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
							redirect("topic.php?tid=$tid&start=$start");

						} else {
							show_error('Ошибка! Редактирование невозможно, прошло более 10 минут!');
						}
					} else {
						show_error('Ошибка! Редактирование невозможно, данная тема закрыта!');
					}
				} else {
					show_error('Ошибка! Сообщение удалено или вы не автор этого сообщения!');
				}
			} else {
				show_error('Ошибка! Слишком длинное или короткое сообщение!');
			}
		} else {
			show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
		}
	} else {
		show_login('Вы не авторизованы, чтобы редактировать сообщения, необходимо');
	}

	render('includes/back', array('link' => 'topic.php?act=edit&amp;tid='.$tid.'&amp;pid='.$pid.'&amp;start='.$start, 'title' => 'Вернуться'));
break;

############################################################################################
##                                     Переход к сообщению                                ##
############################################################################################
case 'viewpost':

	$id = (isset($_GET['id'])) ? abs(intval($_GET['id'])) : 0;

	$querytopic = DB::run() -> querySingle("SELECT COUNT(*) FROM `posts` WHERE `posts_id`<=? AND `posts_topics_id`=? ORDER BY `posts_time` ASC LIMIT 1;", array($id, $tid));
	if (!empty($querytopic)) {
		$end = floor(($querytopic - 1) / $config['forumpost']) * $config['forumpost'];

		redirect("topic.php?tid=$tid&start=$end");

	} else {
		show_error('Ошибка! Выбранная вами тема не существует, возможно она была удалена!');
	}
break;

############################################################################################
##                             Переадресация на последнюю страницу                        ##
############################################################################################
case 'end':

	$querytopic = DB::run() -> querySingle("SELECT `topics_posts` FROM `topics` WHERE `topics_id`=? LIMIT 1;", array($tid));

	if (!empty($querytopic)) {
		$end = floor(($querytopic - 1) / $config['forumpost']) * $config['forumpost'];

		redirect("topic.php?tid=$tid&start=$end");

	} else {
		show_error('Ошибка! Выбранная вами тема не существует, возможно она была удалена!');
	}
break;

default:
	redirect("index.php");
endswitch;

render('includes/back', array('link' => 'index.php', 'title' => 'К форумам', 'icon' => 'reload.gif'));
include_once ('../themes/footer.php');
?>
