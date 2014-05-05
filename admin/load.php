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

if (is_admin(array(101, 102))) {
	show_title('Управление загрузками');

	switch ($act):
	############################################################################################
	##                                    Главная страница                                    ##
	############################################################################################
		case 'index':

			$querydown = DB::run() -> query("SELECT `c`.*, (SELECT SUM(`cats_count`) FROM `cats` WHERE `cats_parent`=`c`.`cats_id`) AS `subcnt`, (SELECT COUNT(*) FROM `downs` WHERE `downs_cats_id`=`cats_id` AND `downs_active`=? AND `downs_time` > ?) AS `new` FROM `cats` `c` ORDER BY `cats_order` ASC;", array(1, SITETIME-86400 * 5));
			$downs = $querydown -> fetchAll();

			if (count($downs) > 0) {
				$output = array();

				foreach ($downs as $row) {
					$id = $row['cats_id'];
					$fp = $row['cats_parent'];
					$output[$fp][$id] = $row;
				}

				foreach($output[0] as $key => $data) {
					echo '<img src="/images/img/dir.gif" alt="image" /> ';
					echo $data['cats_order'].'. <b><a href="load.php?act=down&amp;cid='.$data['cats_id'].'">'.$data['cats_name'].'</a></b> ';

					$subcnt = (empty($data['subcnt'])) ? '' : '/'.$data['subcnt'];
					$new = (empty($data['new'])) ? '' : '/<span style="color:#ff0000">+'.$data['new'].'</span>';

					echo '('.$data['cats_count'] . $subcnt . $new.')<br />';

					if (is_admin(array(101))) {
						echo '<a href="load.php?act=editcats&amp;cid='.$data['cats_id'].'">Редактировать</a> / ';
						echo '<a href="load.php?act=prodelcats&amp;cid='.$data['cats_id'].'">Удалить</a><br />';
					}
					// ----------------------------------------------------//
					if (isset($output[$key])) {
						foreach($output[$key] as $data) {
							echo '<img src="/images/img/right.gif" alt="image" /> ';
							echo $data['cats_order'].'. <b><a href="load.php?act=down&amp;cid='.$data['cats_id'].'">'.$data['cats_name'].'</a></b> ';

							$subcnt = (empty($data['subcnt'])) ? '' : '/'.$data['subcnt'];
							$new = (empty($data['new'])) ? '' : '/<span style="color:#ff0000">+'.$data['new'].'</span>';

							echo '('.$data['cats_count'] . $subcnt . $new.')';

							if (is_admin(array(101))) {
								echo ' (<a href="load.php?act=editcats&amp;cid='.$data['cats_id'].'">Редактировать</a> / ';
								echo '<a href="load.php?act=prodelcats&amp;cid='.$data['cats_id'].'">Удалить</a>)';
							}
							echo '<br />';
						}
					}
				}
			} else {
				show_error('Разделы загрузок еще не созданы!');
			}

			if (is_admin(array(101))) {
				echo '<br /><div class="form">';
				echo '<form action="load.php?act=addcats&amp;uid='.$_SESSION['token'].'" method="post">';
				echo '<b>Заголовок:</b><br />';
				echo '<input type="text" name="name" maxlength="50" />';
				echo '<input type="submit" value="Создать раздел" /></form></div><br />';

				echo '<img src="/images/img/circle.gif" alt="image" /> <a href="load.php?act=newimport">FTP-импорт</a><br />';
				echo '<img src="/images/img/reload.gif" alt="image" /> <a href="load.php?act=restatement&amp;uid='.$_SESSION['token'].'">Пересчитать</a><br />';
			}

			echo '<img src="/images/img/open.gif" alt="image" /> <a href="load.php?act=newfile">Добавить</a><br />';
		break;

		############################################################################################
		##                                      FTP-импорт                                        ##
		############################################################################################
		case 'newimport':
			$config['newtitle'] = 'FTP-импорт';

			if (is_admin(array(101))) {
				if (file_exists(BASEDIR.'/load/loader')) {
					$querydown = DB::run() -> query("SELECT `cats_id`, `cats_parent`, `cats_name` FROM `cats` ORDER BY `cats_order` ASC;");
					$downs = $querydown -> fetchAll();

					if (count($downs) > 0) {
						echo 'Для импорта необходимо загрузить файлы через FTP в папку load/loader, после этого здесь вам нужно выбрать категорию в которую переместить файлы, отметить нужные файлы и нажать импортировать<br /><br />';

						$files = array_diff(scandir(BASEDIR.'/load/loader'), array('.', '..', '.htaccess'));

						$total = count($files);
						if ($total > 0) {
							echo '<div class="form">';
							echo '<form action="load.php?act=addimport&amp;uid='.$_SESSION['token'].'" method="post">';
							echo 'Категория:<br />';

							$output = array();

							foreach ($downs as $row) {
								$i = $row['cats_id'];
								$p = $row['cats_parent'];
								$output[$p][$i] = $row;
							}

							echo '<select name="cid">';
							echo '<option value="0">Выберите категорию</option>';

							foreach ($output[0] as $key => $data) {
								echo '<option value="'.$data['cats_id'].'">'.$data['cats_name'].'</option>';

								if (isset($output[$key])) {
									foreach($output[$key] as $datasub) {
										echo '<option value="'.$datasub['cats_id'].'">– '.$datasub['cats_name'].'</option>';
									}
								}
							}

							echo '</select><br /><br />';

							echo '<input type="checkbox" name="all" onchange="for (i in this.form.elements) this.form.elements[i].checked = this.checked" /> <b>Отметить все</b><br />';

							foreach ($files as $file) {
								$ext = getExtension($file);
								echo '<input type="checkbox" name="files[]" value="'.$file.'" /> <img src="/images/icons/'.icons($ext).'" alt="image" /> '.$file.'<br />';
							}

							echo '<input value="Импортировать" type="submit" /></form></div><br />';

							echo 'Всего файлов: '.$total.'<br /><br />';
						} else {
							show_error('В директории нет файлов для импорта!');
						}
					} else {
						show_error('Категории файлов еще не созданы!');
					}
				} else {
					show_error('Директория для импорта файлов не создана!');
				}
			} else {
				show_error('Импортировать файлы могут только суперадмины!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="load.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                                      FTP-импорт                                        ##
		############################################################################################
		case 'addimport':

			$uid = check($_GET['uid']);
			$cid = abs(intval($_POST['cid']));
			$files = (!empty($_POST['files'])) ? check($_POST['files']) : array();

			if ($uid == $_SESSION['token']) {
				if (!empty($cid)) {
					if (is_writeable(BASEDIR.'/load/files')) {
						$total = count($files);
						if ($total > 0) {
							$downs = DB::run() -> querySingle("SELECT `cats_id` FROM `cats` WHERE `cats_id`=? LIMIT 1;", array($cid));
							if (!empty($downs)) {
								$arrext = explode(',', $config['allowextload']);

								$count = 0;
								foreach ($files as $file) {
									$filename = strtolower($file);
									if (strlen($filename) <= 50) {
										if (preg_match('|^[a-z0-9_\.\-]+$|i', $filename)) {
											$ext = getExtension($filename);
											if (in_array($ext, $arrext) && $ext != 'php') {
												if (!preg_match('/\.(php|pl|cgi|phtml|htaccess)/i', $filename)) {
													if (filesize(BASEDIR.'/load/loader/'.$file) > 0 && filesize(BASEDIR.'/load/loader/'.$file) <= $config['fileupload']) {
														if (!file_exists(BASEDIR.'/load/files/'.$file)) {
															if (file_exists(BASEDIR.'/load/loader/'.$file.'.txt')) {
																$text = file_get_contents(BASEDIR.'/load/loader/'.$file.'.txt');
															} else {
																$text = 'Нет описания';
															}

															if (file_exists(BASEDIR.'/load/loader/'.$file.'.JPG')) {
																rename(BASEDIR.'/load/loader/'.$file.'.JPG', BASEDIR.'/load/screen/'.$filename.'.jpg');
																$screen = $filename.'.jpg';
															} elseif (file_exists(BASEDIR.'/load/loader/'.$file.'.GIF')) {
																rename(BASEDIR.'/load/loader/'.$file.'.GIF', BASEDIR.'/load/screen/'.$filename.'.gif');
																$screen = $filename.'.gif';
															} else {
																$screen = '';
															}

															rename(BASEDIR.'/load/loader/'.$file, BASEDIR.'/load/files/'.$filename);

															DB::run() -> query("UPDATE `cats` SET `cats_count`=`cats_count`+1 WHERE `cats_id`=?", array($cid));
															DB::run() -> query("INSERT INTO `downs` (`downs_cats_id`, `downs_title`, `downs_text`, `downs_link`, `downs_user`, `downs_screen`, `downs_time`, `downs_active`) VALUES (?, ?, ?, ?, ?, ?, ?, ?);", array($cid, $file, $text, $filename, $log, $screen, SITETIME, 1));

															$count++;
														}
													}
												}
											}
										}
									}
								}

								if ($count > 0) {
									echo '<img src="/images/img/open.gif" alt="image" /> <b>Выбранные файлы успешно импортированы</b><br /><br />';
								}

								if ($total != $count) {
									echo 'Не удалось импортировать некоторые файлы!<br />';
									echo 'Возможные причины: недопустимое расширение файлов, большой вес, недопустимое имя файлов или в имени файла присутствуют недопустимые расширения<br /><br />';
								}
							} else {
								show_error('Ошибка! Выбранный вами раздел не существует!');
							}
						} else {
							show_error('Ошибка! Вы не выбрали файлы для импорта!');
						}
					} else {
						show_error('Ошибка! Не установлены атрибуты доступа на дирекоторию с файлами!');
					}
				} else {
					show_error('Ошибка! Вы не выбрали категорию для импорта файлов!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="load.php?act=newimport">Вернуться</a><br />';
		break;

		############################################################################################
		##                                    Добавление файла                                    ##
		############################################################################################
		case 'newfile':
			$config['newtitle'] = 'Публикация нового файла';

			$querydown = DB::run() -> query("SELECT `cats_id`, `cats_parent`, `cats_name` FROM `cats` ORDER BY `cats_order` ASC;");
			$downs = $querydown -> fetchAll();

			if (count($downs) > 0) {
				echo '<div class="form">';
				echo '<form action="load.php?act=addfile&amp;uid='.$_SESSION['token'].'" method="post">';
				echo 'Категория*:<br />';

				$output = array();

				foreach ($downs as $row) {
					$i = $row['cats_id'];
					$p = $row['cats_parent'];
					$output[$p][$i] = $row;
				}

				echo '<select name="cid">';
				echo '<option value="0">Выберите категорию</option>';

				foreach ($output[0] as $key => $data) {
					$selected = ($cid == $data['cats_id']) ? ' selected="selected"' : '';
					echo '<option value="'.$data['cats_id'].'"'.$selected.'>'.$data['cats_name'].'</option>';

					if (isset($output[$key])) {
						foreach($output[$key] as $datasub) {
							$selected = ($cid == $datasub['cats_id']) ? ' selected="selected"' : '';
							echo '<option value="'.$datasub['cats_id'].'"'.$selected.'>– '.$datasub['cats_name'].'</option>';
						}
					}
				}

				echo '</select><br />';

				echo 'Название*:<br />';
				echo '<input type="text" name="title" size="50" maxlength="50" /><br />';
				echo 'Описание*:<br />';
				echo '<textarea cols="25" rows="10" name="text"></textarea><br />';
				echo 'Автор файла:<br />';
				echo '<input type="text" name="author" maxlength="50" /><br />';
				echo 'Сайт автора:<br />';
				echo '<input type="text" name="site" maxlength="50" value="http://" /><br />';

				echo '<input value="Продолжить" type="submit" /></form></div><br />';

				echo 'Все поля отмеченные знаком *, обязательны для заполнения<br />';
				echo 'Файл и скриншот вы сможете загрузить после добавления описания<br />';
				echo 'Если вы ошиблись в названии или описании файла, вы всегда можете его отредактировать<br /><br />';
			} else {
				show_error('Категории файлов еще не созданы!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="load.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                                  Публикация файла                                      ##
		############################################################################################
		case 'addfile':

			$config['newtitle'] = 'Публикация нового файла';

			$uid = check($_GET['uid']);
			$cid = abs(intval($_POST['cid']));
			$title = check($_POST['title']);
			$text = check($_POST['text']);
			$author = (!empty($_POST['author'])) ? check($_POST['author']) : '';
			$site = ($_POST['site'] != 'http://') ? check($_POST['site']) : '';

			if ($uid == $_SESSION['token']) {
				if (!empty($cid)) {
					if (utf_strlen($title) >= 5 && utf_strlen($title) < 50) {
						if (utf_strlen($text) >= 10 && utf_strlen($text) < 5000) {
							if (utf_strlen($author) <= 50) {
								if (utf_strlen($site) <= 50) {
									if (empty($site) || preg_match('#^http://([а-яa-z0-9_\-\.])+(\.([а-яa-z0-9\/])+)+$#u', $site)) {
										$downs = DB::run() -> querySingle("SELECT `cats_id` FROM `cats` WHERE `cats_id`=? LIMIT 1;", array($cid));
										if (!empty($downs)) {
											$downtitle = DB::run() -> querySingle("SELECT `downs_title` FROM `downs` WHERE `downs_title`=? LIMIT 1;", array($title));
											if (empty($downtitle)) {
												$text = no_br($text);

												DB::run() -> query("UPDATE `cats` SET `cats_count`=`cats_count`+1 WHERE `cats_id`=?", array($cid));
												DB::run() -> query("INSERT INTO `downs` (`downs_cats_id`, `downs_title`, `downs_text`, `downs_link`, `downs_user`, `downs_author`, `downs_site`, `downs_screen`, `downs_time`, `downs_active`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?);", array($cid, $title, $text, '', $log, $author, $site, '', SITETIME, 1));

												$lastid = DB::run() -> lastInsertId();

												$_SESSION['note'] = 'Данные успешно добавлены!';
												redirect("load.php?act=editdown&id=$lastid");
											} else {
												show_error('Ошибка! Название '.$title.' уже имеется в файлах!');
											}
										} else {
											show_error('Ошибка! Выбранный вами раздел не существует!');
										}
									} else {
										show_error('Ошибка! Недопустимый адрес сайта, необходим формат http://site.domen!');
									}
								} else {
									show_error('Ошибка! Слишком длинный адрес сайта (не более 50 символов)!');
								}
							} else {
								show_error('Ошибка! Слишком длинный ник (логин) автора (не более 50 символов)!');
							}
						} else {
							show_error('Ошибка! Слишком длинный или короткий текст описания (от 10 до 5000 символов)!');
						}
					} else {
						show_error('Ошибка! Слишком длинное или короткое название (от 5 до 50 символов)!');
					}
				} else {
					show_error('Ошибка! Вы не выбрали категорию для добавления файла!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="load.php?act=newfile&amp;cid='.$cid.'">Вернуться</a><br />';
		break;

		############################################################################################
		##                                    Пересчет счетчиков                                  ##
		############################################################################################
		case 'restatement':

			$uid = check($_GET['uid']);

			if (is_admin(array(101))) {
				if ($uid == $_SESSION['token']) {
					restatement('load');

					$_SESSION['note'] = 'Все данные успешно пересчитаны!';
					redirect("load.php");
				} else {
					show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
				}
			} else {
				show_error('Ошибка! Пересчитывать сообщения могут только суперадмины!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="load.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                                    Добавление разделов                                 ##
		############################################################################################
		case 'addcats':

			$uid = check($_GET['uid']);
			$name = check($_POST['name']);

			if (is_admin(array(101))) {
				if ($uid == $_SESSION['token']) {
					if (utf_strlen($name) >= 4 && utf_strlen($name) < 50) {
						$maxorder = DB::run() -> querySingle("SELECT IFNULL(MAX(`cats_order`),0)+1 FROM `cats`;");
						DB::run() -> query("INSERT INTO `cats` (`cats_order`, `cats_name`) VALUES (?, ?);", array($maxorder, $name));

						$_SESSION['note'] = 'Новый раздел успешно добавлен!';
						redirect("load.php");
					} else {
						show_error('Ошибка! Слишком длинное или короткое название раздела!');
					}
				} else {
					show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
				}
			} else {
				show_error('Ошибка! Добавлять разделы могут только суперадмины!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="load.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                          Подготовка к редактированию разделов                          ##
		############################################################################################
		case 'editcats':

			if (is_admin(array(101))) {
				$downs = DB::run() -> queryFetch("SELECT * FROM `cats` WHERE `cats_id`=? LIMIT 1;", array($cid));

				if (!empty($downs)) {
					echo '<b><big>Редактирование</big></b><br /><br />';

					echo '<div class="form">';
					echo '<form action="load.php?act=addeditcats&amp;cid='.$cid.'&amp;uid='.$_SESSION['token'].'" method="post">';
					echo 'Раздел: <br />';
					echo '<input type="text" name="name" maxlength="50" value="'.$downs['cats_name'].'" /><br />';

					$query = DB::run() -> query("SELECT `cats_id`, `cats_name`, `cats_parent` FROM `cats` WHERE `cats_parent`=0 ORDER BY `cats_order` ASC;");
					$section = $query -> fetchAll();

					echo 'Родительский раздел:<br />';
					echo '<select name="parent">';
					echo '<option value="0">Основной раздел</option>';

					foreach ($section as $data) {
						if ($cid != $data['cats_id']) {
							$selected = ($downs['cats_parent'] == $data['cats_id']) ? ' selected="selected"' : '';
							echo '<option value="'.$data['cats_id'].'"'.$selected.'>'.$data['cats_name'].'</option>';
						}
					}
					echo '</select><br />';

					echo 'Положение: <br />';
					echo '<input type="text" name="order" maxlength="2" value="'.$downs['cats_order'].'" /><br /><br />';

					echo '<input type="submit" value="Изменить" /></form></div><br />';
				} else {
					show_error('Ошибка! Данного раздела не существует!');
				}
			} else {
				show_error('Ошибка! Изменять разделы могут только суперадмины!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="load.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                                 Редактирование разделов                                ##
		############################################################################################
		case 'addeditcats':

			$uid = check($_GET['uid']);
			$name = check($_POST['name']);
			$parent = abs(intval($_POST['parent']));
			$order = abs(intval($_POST['order']));

			if (is_admin(array(101))) {
				if ($uid == $_SESSION['token']) {
					if (utf_strlen($name) >= 4 && utf_strlen($name) < 50) {
						if ($cid != $parent) {
							$downs = DB::run() -> queryFetch("SELECT `cats_id` FROM `cats` WHERE `cats_parent`=? LIMIT 1;", array($cid));

							if (empty($downs) || $parent == 0) {
								DB::run() -> query("UPDATE `cats` SET `cats_order`=?, `cats_parent`=?, `cats_name`=? WHERE `cats_id`=?;", array($order, $parent, $name, $cid));

								$_SESSION['note'] = 'Раздел успешно отредактирован!';
								redirect("load.php");
							} else {
								show_error('Ошибка! Данный раздел имеет подкатегории!');
							}
						} else {
							show_error('Ошибка! Недопустимый выбор родительского раздела!');
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

			echo '<img src="/images/img/reload.gif" alt="image" /> <a href="load.php?act=editcats&amp;cid='.$cid.'">Вернуться</a><br />';
			echo '<img src="/images/img/back.gif" alt="image" /> <a href="load.php">Категории</a><br />';
		break;

		############################################################################################
		##                                  Подтвержение удаления                                 ##
		############################################################################################
		case 'prodelcats':

			if (is_admin(array(101))) {
				$downs = DB::run() -> queryFetch("SELECT `c1`.*, count(`c2`.`cats_id`) AS `subcnt` FROM `cats` `c1` LEFT JOIN `cats` `c2` ON `c2`.`cats_parent` = `c1`.`cats_id` WHERE `c1`.`cats_id`=? GROUP BY `cats_id` LIMIT 1;", array($cid));

				if (!empty($downs['cats_id'])) {
					if (empty($downs['subcnt'])) {
						echo 'Вы уверены что хотите удалить раздел <b>'.$downs['cats_name'].'</b> в загрузках?<br />';
						echo '<img src="/images/img/error.gif" alt="image" /> <b><a href="load.php?act=delcats&amp;cid='.$cid.'&amp;uid='.$_SESSION['token'].'">Да, уверен!</a></b><br /><br />';
					} else {
						show_error('Ошибка! Данный раздел имеет подкатегории!');
					}
				} else {
					show_error('Ошибка! Данного раздела не существует!');
				}
			} else {
				show_error('Ошибка! Удалять разделы могут только суперадмины!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="load.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                                    Удаление раздела                                    ##
		############################################################################################
		case 'delcats':

			$uid = check($_GET['uid']);

			if (is_admin(array(101)) && $log == $config['nickname']) {
				if ($uid == $_SESSION['token']) {
					$downs = DB::run() -> queryFetch("SELECT `c1`.*, count(`c2`.`cats_id`) AS `subcnt` FROM `cats` `c1` LEFT JOIN `cats` `c2` ON `c2`.`cats_parent` = `c1`.`cats_id` WHERE `c1`.`cats_id`=? GROUP BY `cats_id` LIMIT 1;", array($cid));

					if (!empty($downs['cats_id'])) {
						if (empty($downs['subcnt'])) {
							if (is_writeable(BASEDIR.'/load/files')) {
								$querydel = DB::run() -> query("SELECT `downs_link`, `downs_screen` FROM `downs` WHERE `downs_cats_id`=?;", array($cid));
								$arr_script = $querydel -> fetchAll();

								DB::run() -> query("DELETE FROM `commload` WHERE `commload_cats`=?;", array($cid));
								DB::run() -> query("DELETE FROM `downs` WHERE `downs_cats_id`=?;", array($cid));
								DB::run() -> query("DELETE FROM `cats` WHERE `cats_id`=?;", array($cid));

								foreach ($arr_script as $delfile) {
									if (!empty($delfile['downs_link']) && file_exists(BASEDIR.'/load/files/'.$delfile['downs_link'])) {
										unlink(BASEDIR.'/load/files/'.$delfile['downs_link']);
									}

									unlink_image('load/screen/', $delfile['downs_screen']);
								}

								$_SESSION['note'] = 'Раздел успешно удален!';
								redirect("load.php");
							} else {
								show_error('Ошибка! Не установлены атрибуты доступа на дирекоторию с файлами!');
							}
						} else {
							show_error('Ошибка! Данный раздел имеет подкатегории!');
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

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="load.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                                       Просмотр файлов                                  ##
		############################################################################################
		case 'down':

			$cats = DB::run() -> queryFetch("SELECT * FROM `cats` WHERE `cats_id`=? LIMIT 1;", array($cid));

			echo '<a href="#down"><img src="/images/img/downs.gif" alt="Вниз" /></a> <a href="load.php">Категории</a> / ';

			if (!empty($cats['cats_parent'])) {
				$podcats = DB::run() -> queryFetch("SELECT `cats_id`, `cats_name` FROM `cats` WHERE `cats_id`=? LIMIT 1;", array($cats['cats_parent']));
				echo '<a href="load.php?act=down&amp;cid='.$podcats['cats_id'].'">'.$podcats['cats_name'].'</a> / ';
			}

			echo '<a href="load.php?act=newfile&amp;cid='.$cid.'">Загрузить файл</a><br /><br />';

			if ($cats > 0) {
				$config['newtitle'] = $cats['cats_name'];

				echo '<img src="/images/img/open_dir.gif" alt="image" /> <b>'.$cats['cats_name'].'</b> (Файлов: '.$cats['cats_count'].')';
				echo ' (<a href="/load/down.php?cid='.$cid.'&amp;start='.$start.'">Обзор</a>)';
				echo '<hr />';

				$querysub = DB::run() -> query("SELECT * FROM `cats` WHERE `cats_parent`=?;", array($cid));
				$sub = $querysub -> fetchAll();

				if (count($sub) > 0 && $start == 0) {
					foreach($sub as $subdata) {
						echo '<div class="b"><img src="/images/img/dir.gif" alt="image" /> ';
						echo '<b><a href="load.php?act=down&amp;cid='.$subdata['cats_id'].'">'.$subdata['cats_name'].'</a></b> ('.$subdata['cats_count'].')</div>';
					}
					echo '<hr />';
				}

				$total = DB::run() -> querySingle("SELECT count(*) FROM `downs` WHERE `downs_cats_id`=? AND `downs_active`=?;", array($cid, 1));

				if ($total > 0) {
					if ($start >= $total) {
						$start = 0;
					}

					$querydown = DB::run() -> query("SELECT * FROM `downs` WHERE `downs_cats_id`=? AND `downs_active`=? ORDER BY `downs_time` DESC LIMIT ".$start.", ".$config['downlist'].";", array($cid, 1));

					$is_admin = (is_admin(array(101)) && $log == $config['nickname']);

					if ($is_admin) {
						echo '<form action="load.php?act=deldown&amp;cid='.$cid.'&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';
					}

					 while ($data = $querydown -> fetch()) {
						$filesize = (!empty($data['downs_link'])) ? read_file(BASEDIR.'/load/files/'.$data['downs_link']) : 0;

						echo '<div class="b">';
						echo '<img src="/images/img/zip.gif" alt="image" /> ';
						echo '<b><a href="/load/down.php?act=view&amp;id='.$data['downs_id'].'">'.$data['downs_title'].'</a></b> ('.$filesize.')<br />';

						if ($is_admin) {
							echo '<input type="checkbox" name="del[]" value="'.$data['downs_id'].'" /> ';
						}

						echo '<a href="load.php?act=editdown&amp;cid='.$cid.'&amp;id='.$data['downs_id'].'&amp;start='.$start.'">Редактировать</a> / ';
						echo '<a href="load.php?act=movedown&amp;cid='.$cid.'&amp;id='.$data['downs_id'].'&amp;start='.$start.'">Переместить</a></div>';

						echo '<div>';

						echo 'Скачиваний: '.$data['downs_load'].'<br />';

						$raiting = (!empty($data['downs_rated'])) ? round($data['downs_raiting'] / $data['downs_rated'], 1) : 0;

						echo 'Рейтинг: <b>'.$raiting.'</b> (Голосов: '.$data['downs_rated'].')<br />';
						echo '<a href="/load/down.php?act=comments&amp;id='.$data['downs_id'].'">Комментарии</a> ('.$data['downs_comments'].')</div>';
					}

					if ($is_admin) {
						echo '<br /><input type="submit" value="Удалить выбранное" /></form>';
					}

					page_strnavigation('load.php?act=down&amp;cid='.$cid.'&amp;', $config['downlist'], $start, $total);
				} else {
					show_error('В данном разделе еще нет файлов!');
				}
			} else {
				show_error('Ошибка! Данного раздела не существует!');
			}

			echo '<img src="/images/img/open.gif" alt="image" /> <a href="load.php?act=newfile&amp;cid='.$cid.'">Добавить</a><br />';
			echo '<img src="/images/img/reload.gif" alt="image" /> <a href="load.php">Категории</a><br />';
		break;

		############################################################################################
		##                            Подготовка к редактированию файла                           ##
		############################################################################################
		case 'editdown':

			$config['newtitle'] = 'Редактирование файла';

			$new = DB::run() -> queryFetch("SELECT `downs`.*, `cats`.* FROM `downs` LEFT JOIN `cats` ON `downs`.`downs_cats_id`=`cats`.`cats_id` WHERE `downs_id`=? LIMIT 1;", array($id));

			if (!empty($new)) {
				echo '<a href="#down"><img src="/images/img/downs.gif" alt="Вниз" /></a> <a href="load.php">Категории</a> / ';

				if (!empty($new['cats_parent'])) {
					$podcats = DB::run() -> queryFetch("SELECT `cats_id`, `cats_name` FROM `cats` WHERE `cats_id`=? LIMIT 1;", array($new['cats_parent']));
					echo '<a href="load.php?act=down&amp;cid='.$podcats['cats_id'].'">'.$podcats['cats_name'].'</a> / ';
				}

				echo '<a href="/load/down.php?act=view&amp;id='.$id.'">Обзор файла</a><br /><br />';

				if (empty($new['downs_link'])) {
					echo '<b><big>Загрузка файла</big></b><br /><br />';

					echo '<div class="form">';
					echo '<form action="load.php?act=loadfile&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'" method="post" enctype="multipart/form-data">';
					echo 'Прикрепить файл* ('.$config['allowextload'].'):<br /><input type="file" name="loadfile" /><br />';
					echo '<input value="Загрузить" type="submit" /></form></div><br />';

					echo '<div class="form">';
					echo '<form action="load.php?act=copyfile&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'" method="post">';
					echo 'Импорт файла*:<br /><input type="text" name="loadfile" value="http://" /><br />';
					echo '<input value="Импортировать" type="submit" /></form></div><br />';

				} else {

					echo '<img src="/images/img/download.gif" alt="image" /> <b><a href="/load/files/'.$new['downs_link'].'">'.$new['downs_link'].'</a></b> ('.read_file(BASEDIR.'/load/files/'.$new['downs_link']).') (<a href="load.php?act=delfile&amp;id='.$id.'" onclick="return confirm(\'Вы действительно хотите удалить данный файл?\')">Удалить</a>)<br />';

					$ext = getExtension($new['downs_link']);
					if ($ext != 'jpg' && $ext != 'jpeg' && $ext != 'gif' && $ext != 'png') {
						if (empty($new['downs_screen'])) {
							echo '<br /><b><big>Загрузка скриншота</big></b><br /><br />';
							echo '<div class="form">';
							echo '<form action="load.php?act=loadscreen&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'" method="post" enctype="multipart/form-data">';
							echo 'Прикрепить скрин (jpg,jpeg,gif,png):<br /><input type="file" name="screen" /><br />';
							echo '<input value="Загрузить" type="submit" /></form></div><br />';
						} else {
							echo '<img src="/images/img/gallery.gif" alt="image" /> <b><a href="/load/screen/'.$new['downs_screen'].'">'.$new['downs_screen'].'</a></b> ('.read_file(BASEDIR.'/load/screen/'.$new['downs_screen']).') (<a href="load.php?act=delscreen&amp;id='.$id.'" onclick="return confirm(\'Вы действительно хотите удалить данный скриншот?\')">Удалить</a>)<br /><br />';
							echo resize_image('load/screen/', $new['downs_screen'], $config['previewsize']).'<br />';
						}
					}
				}

				echo '<br />';

				echo '<b><big>Редактирование</big></b><br /><br />';
				echo '<div class="form">';
				echo '<form action="load.php?act=changedown&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'" method="post">';

				$new['downs_text'] = yes_br(nosmiles($new['downs_text']));

				echo 'Название*:<br />';
				echo '<input type="text" name="title" size="50" maxlength="50" value="'.$new['downs_title'].'" /><br />';
				echo 'Описание*:<br />';
				echo '<textarea cols="25" rows="5" name="text">'.$new['downs_text'].'</textarea><br />';
				echo 'Автор файла:<br />';
				echo '<input type="text" name="author" maxlength="50" value="'.$new['downs_author'].'" /><br />';
				echo 'Сайт автора:<br />';
				echo '<input type="text" name="site" maxlength="50" value="'.$new['downs_site'].'" /><br />';
				echo 'Имя файла*:<br />';
				echo '<input type="text" name="loadfile" maxlength="50" value="'.$new['downs_link'].'" /><br />';

				echo '<input value="Изменить" type="submit" /></form></div><br />';
			} else {
				show_error('Данного файла не существует!');
			}

			echo '<img src="/images/img/reload.gif" alt="image" /> <a href="load.php">Категории</a><br />';
		break;

		############################################################################################
		##                                  Редактирование файла                                  ##
		############################################################################################
		case 'changedown':

			$uid = check($_GET['uid']);
			$title = check($_POST['title']);
			$text = check($_POST['text']);
			$author = (!empty($_POST['author'])) ? check($_POST['author']) : '';
			$site = ($_POST['site'] != 'http://') ? check($_POST['site']) : '';
			$loadfile = check(strtolower($_POST['loadfile']));

			if ($uid == $_SESSION['token']) {
				if (utf_strlen($title) >= 5 && utf_strlen($title) <= 50) {
					if (utf_strlen($text) >= 10 && utf_strlen($text) <= 5000) {
						if (utf_strlen($author) <= 50) {
							if (utf_strlen($site) <= 50) {
								if (empty($site) || preg_match('#^http://([а-яa-z0-9_\-\.])+(\.([а-яa-z0-9\/])+)+$#u', $site)) {
									if (strlen($loadfile) <= 50) {
										if (!preg_match('/\.(php|pl|cgi|phtml|htaccess)/i', $loadfile)) {
											$new = DB::run() -> queryFetch("SELECT * FROM `downs` WHERE `downs_id`=?;", array($id));
											if (!empty($new)) {

												$downlink = DB::run() -> querySingle("SELECT `downs_link` FROM `downs` WHERE `downs_link`=? AND `downs_id`<>? LIMIT 1;", array($loadfile, $id));
												if (empty($downlink)) {

													$downtitle = DB::run() -> querySingle("SELECT `downs_title` FROM `downs` WHERE `downs_title`=? AND `downs_id`<>? LIMIT 1;", array($title, $id));
													if (empty($downtitle)) {

														$text = no_br($text);

														if (!empty($loadfile) && $loadfile != $new['downs_link'] && file_exists(BASEDIR.'/load/files/'.$new['downs_link'])) {

															$oldext = getExtension($new['downs_link']);
															$newext = getExtension($loadfile);

															if ($oldext == $newext) {

																$screen = $new['downs_screen'];
																rename(BASEDIR.'/load/files/'.$new['downs_link'], BASEDIR.'/load/files/'.$loadfile);

																if (!empty($new['downs_screen']) && file_exists(BASEDIR.'/load/screen/'.$new['downs_screen'])) {

																	$screen = $loadfile.'.'.getExtension($new['downs_screen']);
																	rename(BASEDIR.'/load/screen/'.$new['downs_screen'], BASEDIR.'/load/screen/'.$screen);
																	unlink_image('load/screen/', $new['downs_screen']);
																}
																DB::run() -> query("UPDATE `downs` SET `downs_link`=?, `downs_screen`=? WHERE `downs_id`=?;", array($loadfile, $screen, $id));
															}
														}

														DB::run() -> query("UPDATE `downs` SET `downs_title`=?, `downs_text`=?, `downs_author`=?, `downs_site`=?, `downs_time`=? WHERE `downs_id`=?;", array($title, $text, $author, $site, $new['downs_time'], $id));

														$_SESSION['note'] = 'Данные успешно изменены!';
														redirect("load.php?act=editdown&id=$id");

													} else {
														show_error('Ошибка! Название '.$title.' уже имеется в общих файлах!');
													}
												} else {
													show_error('Ошибка! Файл '.$loadfile.' уже присутствует в общих файлах!');
												}
											} else {
												show_error('Данного файла не существует!');
											}
										} else {
											show_error('Ошибка! В названии файла присутствуют недопустимые расширения!');
										}
									} else {
										show_error('Ошибка! Слишком длинное имя файла (не более 50 символов)!');
									}
								} else {
									show_error('Ошибка! Недопустимый адрес сайта, необходим формат http://site.domen!');
								}
							} else {
								show_error('Ошибка! Слишком длинный адрес сайта (не более 50 символов)!');
							}
						} else {
							show_error('Ошибка! Слишком длинный ник (логин) автора (до 50 символов)!');
						}
					} else {
						show_error('Ошибка! Слишком длинный или короткий текст описания (от 10 до 5000 символов)!');
					}
				} else {
					show_error('Ошибка! Слишком длинное или короткое название (от 5 до 50 символов)!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="load.php?act=editdown&amp;id='.$id.'">Вернуться</a><br />';
		break;

		############################################################################################
		##                                   Импорт файла                                         ##
		############################################################################################
		case 'copyfile':
			show_title('Импорт файла');

			$loadfile = check($_POST['loadfile']);

			$down = DB::run() -> queryFetch("SELECT * FROM `downs` WHERE `downs_id`=?;", array($id));
			if (!empty($down)) {
				if (empty($down['downs_link'])) {
					if (!empty($loadfile)) {
						$filename = strtolower(basename($loadfile));

						if (strlen($filename) <= 50) {
							if (preg_match('|^[a-z0-9_\.\-]+$|i', $filename)) {
								$arrext = explode(',', $config['allowextload']);
								$ext = getExtension($filename);

								if (in_array($ext, $arrext) && $ext != 'php') {
									if (!preg_match('/\.(php|pl|cgi|phtml|htaccess)/i', $filename)) {
										$downlink = DB::run() -> querySingle("SELECT `downs_link` FROM `downs` WHERE `downs_link`=? LIMIT 1;", array($filename));
										if (empty($downlink)) {
											if (@copy($loadfile, BASEDIR.'/load/files/'.$filename)) {
												@chmod(BASEDIR.'/load/files/'.$filename, 0666);

												copyright_archive(BASEDIR.'/load/files/'.$filename);

												DB::run() -> query("UPDATE `downs` SET `downs_link`=? WHERE `downs_id`=?;", array($filename, $id));

												$_SESSION['note'] = 'Файл успешно импортирован!';
												redirect("load.php?act=editdown&id=$id");
											} else {
												show_error('Ошибка! Не удалось импортировать файл!');
											}
										} else {
											show_error('Ошибка! Файл '.$filename.' уже имеется в общих файлах!');
										}
									} else {
										show_error('Ошибка! В названии файла присутствуют недопустимые расширения!');
									}
								} else {
									show_error('Ошибка! Недопустимое расширение файла!');
								}
							} else {
								show_error('Ошибка! В названии файла присутствуют недопустимые символы!');
							}
						} else {
							show_error('Ошибка! Слишком длинное имя файла (не более 50 символов)!');
						}
					} else {
						show_error('Ошибка! Не указан путь для импорта файла');
					}
				} else {
					show_error('Ошибка! Файл уже загружен!');
				}
			} else {
				show_error('Данного файла не существует!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="load.php?act=editdown&amp;id='.$id.'">Вернуться</a><br />';
		break;

		############################################################################################
		##                                   Загрузка файла                                       ##
		############################################################################################
		case 'loadfile':
			show_title('Загрузка файла');

			$down = DB::run() -> queryFetch("SELECT * FROM `downs` WHERE `downs_id`=?;", array($id));
			if (!empty($down)) {
				if (empty($down['downs_link'])) {
					if (is_uploaded_file($_FILES['loadfile']['tmp_name'])) {
						$filename = check(strtolower($_FILES['loadfile']['name']));

						if (strlen($filename) <= 50) {
							if (preg_match('|^[a-z0-9_\.\-]+$|i', $filename)) {
								$arrext = explode(',', $config['allowextload']);
								$ext = getExtension($filename);

								if (in_array($ext, $arrext) && $ext != 'php') {
									if (!preg_match('/\.(php|pl|cgi|phtml|htaccess)/i', $filename)) {
										if ($_FILES['loadfile']['size'] > 0 && $_FILES['loadfile']['size'] <= $config['fileupload']) {
											$downlink = DB::run() -> querySingle("SELECT `downs_link` FROM `downs` WHERE `downs_link`=? LIMIT 1;", array($filename));
											if (empty($downlink)) {

												move_uploaded_file($_FILES['loadfile']['tmp_name'], BASEDIR.'/load/files/'.$filename);
												@chmod(BASEDIR.'/load/files/'.$filename, 0666);

												copyright_archive(BASEDIR.'/load/files/'.$filename);

												DB::run() -> query("UPDATE `downs` SET `downs_link`=? WHERE `downs_id`=?;", array($filename, $id));

												$_SESSION['note'] = 'Файл успешно загружен!';
												redirect("load.php?act=editdown&id=$id");
											} else {
												show_error('Ошибка! Файл '.$filename.' уже имеется в общих файлах!');
											}
										} else {
											show_error('Ошибка! Максимальный размер загружаемого файла '.formatsize($config['fileupload']).'!');
										}
									} else {
										show_error('Ошибка! В названии файла присутствуют недопустимые расширения!');
									}
								} else {
									show_error('Ошибка! Недопустимое расширение файла!');
								}
							} else {
								show_error('Ошибка! В названии файла присутствуют недопустимые символы!');
							}
						} else {
							show_error('Ошибка! Слишком длинное имя файла (не более 50 символов)!');
						}
					} else {
						show_error('Ошибка! Не удалось загрузить файл!');
					}
				} else {
					show_error('Ошибка! Файл уже загружен!');
				}
			} else {
				show_error('Данного файла не существует!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="load.php?act=editdown&amp;id='.$id.'">Вернуться</a><br />';
		break;

		############################################################################################
		##                                   Загрузка скриншота                                   ##
		############################################################################################
		case 'loadscreen':
			show_title('Загрузка скриншота');

			$down = DB::run() -> queryFetch("SELECT * FROM `downs` WHERE `downs_id`=?;", array($id));
			if (!empty($down)) {
				if (empty($down['downs_screen'])) {
					if (is_uploaded_file($_FILES['screen']['tmp_name'])) {
						$screenname = check(strtolower($_FILES['screen']['name']));
						$screensize = GetImageSize($_FILES['screen']['tmp_name']);
						$ext = getExtension($screenname);

						if ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'gif' || $ext == 'png') {
							if (!preg_match('/\.(php|pl|cgi|phtml|htaccess)/i', $screenname)) {
								if ($_FILES['screen']['size'] > 0 && $_FILES['screen']['size'] <= $config['screenupload']) {
									if ($screensize[0] <= $config['screenupsize'] && $screensize[1] <= $config['screenupsize'] && $screensize[0] >= 100 && $screensize[1] >= 100) {
										// ------------------------------------------------------//
										$handle = upload_image($_FILES['screen'], $down['downs_link']);
										if ($handle) {

											$handle -> process(BASEDIR.'/load/screen/');
											if ($handle -> processed) {

												DB::run() -> query("UPDATE `downs` SET `downs_screen`=? WHERE `downs_id`=?;", array($handle -> file_dst_name, $id));

												$handle -> clean();

												$_SESSION['note'] = 'Скриншот успешно загружен!';
												redirect("load.php?act=editdown&id=$id");
											} else {
												show_error('Ошибка! '.$handle -> error);
											}
										} else {
											show_error('Ошибка! Не удалось загрузить скриншот!');
										}
									} else {
										show_error('Ошибка! Требуемый размер скриншота: от 100 до '.$config['screenupsize'].' px');
									}
								} else {
									show_error('Ошибка! Максимальный размер загружаемого скриншота '.formatsize($config['screenupload']).'!');
								}
							} else {
								show_error('Ошибка! В названии скриншота присутствуют недопустимые расширения!');
							}
						} else {
							show_error('Ошибка! Разрешается загружать скриншоты с расширением jpg, jpeg, gif и png!');
						}
					} else {
						show_error('Ошибка! Вы не загрузили скриншот!');
					}
				} else {
					show_error('Ошибка! Скриншот уже загружен!');
				}
			} else {
				show_error('Данного файла не существует!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="load.php?act=editdown&amp;id='.$id.'">Вернуться</a><br />';
		break;

		############################################################################################
		##                                   Удаление файла                                       ##
		############################################################################################
		case 'delfile':

			$link = DB::run() -> queryFetch("SELECT * FROM `downs` WHERE `downs_id`=?;", array($id));
			if (!empty($link)) {

				if (!empty($link['downs_link']) && file_exists(BASEDIR.'/load/files/'.$link['downs_link'])) {
					unlink(BASEDIR.'/load/files/'.$link['downs_link']);
				}

				unlink_image('load/screen/', $link['downs_screen']);

				DB::run() -> query("UPDATE `downs` SET `downs_link`=?, `downs_screen`=? WHERE `downs_id`=?;", array('', '', $id));

				$_SESSION['note'] = 'Файл успешно удален!';
				redirect("load.php?act=editdown&id=$id");
			} else {
				show_error('Ошибка! Данного файла не существует!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="load.php?act=editdown&amp;id='.$id.'">Вернуться</a><br />';
		break;

		############################################################################################
		##                                    Удаление скриншота                                  ##
		############################################################################################
		case 'delscreen':

			$queryscreen = DB::run() -> querySingle("SELECT `downs_screen` FROM `downs` WHERE `downs_id`=?;", array($id));
			if (!empty($queryscreen)) {

				unlink_image('load/screen/', $queryscreen);

				DB::run() -> query("UPDATE `downs` SET `downs_screen`=? WHERE `downs_id`=?;", array('', $id));

				$_SESSION['note'] = 'Скриншот успешно удален!';
				redirect("load.php?act=editdown&id=$id");
			} else {
				show_error('Ошибка! Данного файла не существует!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="load.php?act=editdown&amp;id='.$id.'">Вернуться</a><br />';
		break;

		############################################################################################
		##                               Подготовка к перемещению файла                           ##
		############################################################################################
		case 'movedown':

			$downs = DB::run() -> queryFetch("SELECT * FROM `downs` WHERE `downs_id`=? LIMIT 1;", array($id));

			if (!empty($downs)) {
				echo '<img src="/images/img/download.gif" alt="image" /> <b>'.$downs['downs_title'].'</b> ('.read_file(BASEDIR.'/load/files/'.$downs['downs_link']).')<br /><br />';

				$querycats = DB::run() -> query("SELECT `cats_id`, `cats_parent`, `cats_name` FROM `cats` ORDER BY `cats_order` ASC;");
				$cats = $querycats -> fetchAll();

				if (count($cats) > 0) {
					$output = array();
					foreach ($cats as $row) {
						$i = $row['cats_id'];
						$p = $row['cats_parent'];
						$output[$p][$i] = $row;
					}

					echo '<div class="form"><form action="load.php?act=addmovedown&amp;cid='.$downs['downs_cats_id'].'&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'" method="post">';

					echo 'Выберите раздел для перемещения:<br />';
					echo '<select name="section">';
					echo '<option value="0">Список разделов</option>';

					foreach ($output[0] as $key => $data) {
						if ($downs['downs_cats_id'] != $data['cats_id']) {
							echo '<option value="'.$data['cats_id'].'">'.$data['cats_name'].'</option>';
						}

						if (isset($output[$key])) {
							foreach($output[$key] as $datasub) {
								if ($downs['downs_cats_id'] != $datasub['cats_id']) {
									echo '<option value="'.$datasub['cats_id'].'">– '.$datasub['cats_name'].'</option>';
								}
							}
						}
					}

					echo '</select>';

					echo '<input type="submit" value="Переместить" /></form></div><br />';
				} else {
					show_error('Разделы загрузок еще не созданы!');
				}
			} else {
				show_error('Ошибка! Данного файла не существует!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="load.php?act=down&amp;cid='.$cid.'&amp;start='.$start.'">Вернуться</a><br />';
		break;

		############################################################################################
		##                                    Перемещение файла                                   ##
		############################################################################################
		case 'addmovedown':

			$uid = check($_GET['uid']);
			$section = abs(intval($_POST['section']));

			if ($uid == $_SESSION['token']) {
				$querycats = DB::run() -> querySingle("SELECT `cats_id` FROM `cats` WHERE `cats_id`=? LIMIT 1;", array($section));
				if (!empty($querycats)) {
					$querydown = DB::run() -> querySingle("SELECT `downs_id` FROM `downs` WHERE `downs_id`=? LIMIT 1;", array($id));
					if (!empty($querydown)) {
						DB::run() -> query("UPDATE `downs` SET `downs_cats_id`=? WHERE `downs_id`=?;", array($section, $id));
						DB::run() -> query("UPDATE `commload` SET `commload_cats`=? WHERE `commload_down`=?;", array($section, $id));
						// Обновление счетчиков
						DB::run() -> query("UPDATE `cats` SET `cats_count`=`cats_count`+1 WHERE `cats_id`=?", array($section));
						DB::run() -> query("UPDATE `cats` SET `cats_count`=`cats_count`-1 WHERE `cats_id`=?", array($cid));

						$_SESSION['note'] = 'Файл успешно перемещен!';
						redirect("load.php?act=down&cid=$section");
					} else {
						show_error('Ошибка! Файла для перемещения не существует!');
					}
				} else {
					show_error('Ошибка! Выбранного раздела не существует!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="load.php?act=movedown&amp;id='.$id.'">Вернуться</a><br />';
			echo '<img src="/images/img/reload.gif" alt="image" /> <a href="load.php?act=down&amp;cid='.$cid.'">К разделам</a><br />';
		break;

		############################################################################################
		##                                   Удаление файлов                                      ##
		############################################################################################
		case 'deldown':

			$uid = check($_GET['uid']);
			if (isset($_POST['del'])) {
				$del = intar($_POST['del']);
			} else {
				$del = 0;
			}

			if (is_admin(array(101)) && $log == $config['nickname']) {
				if ($uid == $_SESSION['token']) {
					if ($del > 0) {
						$del = implode(',', $del);

						if (is_writeable(BASEDIR.'/load/files')) {
							$querydel = DB::run() -> query("SELECT `downs_link`, `downs_screen` FROM `downs` WHERE `downs_id` IN (".$del.");");
							$arr_script = $querydel -> fetchAll();

							DB::run() -> query("DELETE FROM `commload` WHERE `commload_down` IN (".$del.");");
							$deldowns = DB::run() -> exec("DELETE FROM `downs` WHERE `downs_id` IN (".$del.");");
							// Обновление счетчиков
							DB::run() -> query("UPDATE `cats` SET `cats_count`=`cats_count`-? WHERE `cats_id`=?", array($deldowns, $cid));

							foreach ($arr_script as $delfile) {
								if (!empty($delfile['downs_link']) && file_exists(BASEDIR.'/load/files/'.$delfile['downs_link'])) {
									unlink(BASEDIR.'/load/files/'.$delfile['downs_link']);
								}

								unlink_image('load/screen/', $delfile['downs_screen']);
							}

							$_SESSION['note'] = 'Выбранные файлы успешно удалены!';
							redirect("load.php?act=down&cid=$cid&start=$start");
						} else {
							show_error('Ошибка! Не установлены атрибуты доступа на дирекоторию с файлами!');
						}
					} else {
						show_error('Ошибка! Отсутствуют выбранные файлы!');
					}
				} else {
					show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
				}
			} else {
				show_error('Ошибка! Удалять файлы могут только суперадмины!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="load.php?act=down&amp;cid='.$cid.'&amp;start='.$start.'">Вернуться</a><br />';
		break;

	default:
		redirect("load.php");
	endswitch;

	echo '<img src="/images/img/panel.gif" alt="image" /> <a href="index.php">В админку</a><br />';

} else {
	redirect('/index.php');
}

include_once ('../themes/footer.php');
?>
