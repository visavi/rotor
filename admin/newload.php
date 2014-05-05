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
if (isset($_GET['start'])) {
	$start = abs(intval($_GET['start']));
} else {
	$start = 0;
}
if (isset($_GET['id'])) {
	$id = abs(intval($_GET['id']));
} else {
	$id = 0;
}

if (is_admin()) {
	show_title('Просмотр новых файлов');

	switch ($act):
	############################################################################################
	##                                    Главная страница                                    ##
	############################################################################################
		case 'index':

			$total = DB::run() -> querySingle("SELECT count(*) FROM `downs` WHERE `downs_active`=?;", array(0));

			if ($total > 0) {
				if ($start >= $total) {
					$start = 0;
				}

				$querynew = DB::run() -> query("SELECT `downs`.*, `cats_name` FROM `downs` LEFT JOIN `cats` ON `downs`.`downs_cats_id`=`cats`.`cats_id` WHERE `downs_active`=? ORDER BY `downs_app` DESC, `downs_time` DESC  LIMIT ".$start.", ".$config['downlist'].";", array(0));

				echo '<form action="newload.php?act=deldown&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';

				while ($data = $querynew -> fetch()) {
					echo '<div class="b">';
					echo '<input type="checkbox" name="del[]" value="'.$data['downs_id'].'" /> ';

					if (empty($data['downs_app'])) {
						echo '<img src="/images/img/download.gif" alt="image" /> ';
					} else {
						echo '<img src="/images/img/open.gif" alt="image" /> ';
					}

					echo '<b><a href="newload.php?act=view&amp;id='.$data['downs_id'].'">'.$data['downs_title'].'</a></b> ('.date_fixed($data['downs_time']).')</div>';
					echo '<div>';
					echo 'Категория: <a href="/load/down.php?cid='.$data['downs_cats_id'].'">'.$data['cats_name'].'</a><br />';
					echo 'Добавлено: '.profile($data['downs_user']).'<br />';
					if (!empty($data['downs_link'])) {
						echo 'Файл: '.$data['downs_link'].' ('.read_file(BASEDIR.'/load/files/'.$data['downs_link']).')<br />';
					} else {
						echo 'Файл: Не загружен<br />';
					}
					if (!empty($data['downs_screen'])) {
						echo 'Скрин: '.$data['downs_screen'].' ('.read_file(BASEDIR.'/load/screen/'.$data['downs_screen']).')<br />';
					} else {
						echo 'Скрин: Не загружен<br />';
					}
					echo '</div>';
				}

				echo '<br /><input type="submit" value="Удалить выбранное" /></form>';

				page_strnavigation('newload.php?', $config['downlist'], $start, $total);

				echo 'Всего файлов: <b>'.$total.'</b><br /><br />';
			} else {
				show_error('Новых файлов еще нет!');
			}
		break;

		############################################################################################
		##                                  Просмотр файла                                      ##
		############################################################################################
		case 'view':

			$new = DB::run() -> queryFetch("SELECT * FROM `downs` WHERE `downs_id`=?;", array($id));

			if (!empty($new)) {
				if (empty($new['downs_active'])) {

					$querydown = DB::run() -> query("SELECT `cats_id`, `cats_parent`, `cats_name` FROM `cats` ORDER BY `cats_order` ASC;");
					$downs = $querydown -> fetchAll();

					if (count($downs) > 0) {
						echo '<a href="#down"><img src="/images/img/downs.gif" alt="Вниз" /></a> ';

						if (is_admin(array(101)) && $log == $config['nickname']) {
							echo '<a href="newload.php?act=allow&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'" onclick="return confirm(\'Вы подтверждаете публикацию файла?\')">Опубликовать</a> / ';
						}

						echo '<a href="newload.php?act=deldown&amp;del='.$new['downs_id'].'&amp;uid='.$_SESSION['token'].'" onclick="return confirm(\'Вы подтверждаете удаление файла?\')">Удалить файл</a><hr />';

						if (!empty($new['downs_link'])) {
							echo '<img src="/images/img/download.gif" alt="image" /> <b><a href="/load/files/'.$new['downs_link'].'">'.$new['downs_link'].'</a></b> ('.read_file(BASEDIR.'/load/files/'.$new['downs_link']).')  (<a href="newload.php?act=delfile&amp;id='.$id.'" onclick="return confirm(\'Вы действительно хотите удалить данный файл?\')">Удалить</a>)<br />';
						} else {
							echo '<img src="/images/img/download.gif" alt="image" /> <b>Не загружен</b><br />';
						}

						if (!empty($new['downs_screen'])) {
							echo '<img src="/images/img/gallery.gif" alt="image" /> <b><a href="/load/screen/'.$new['downs_screen'].'">'.$new['downs_screen'].'</a></b> ('.read_file(BASEDIR.'/load/screen/'.$new['downs_screen']).') (<a href="newload.php?act=delscreen&amp;id='.$id.'" onclick="return confirm(\'Вы действительно хотите удалить данный скриншот?\')">Удалить</a>)<br /><br />';
							echo resize_image('load/screen/', $new['downs_screen'], $config['previewsize']).'<br />';
						} else {
							echo '<img src="/images/img/gallery.gif" alt="image" /> <b>Не загружен</b><br />';
						}

						echo '<br /><b><big>Редактирование</big></b><br /><br />';

						echo 'Добавлено: <b>'.profile($new['downs_user']).'</b> '.user_visit($new['downs_user']).'<br />';
						echo 'Время последнего изменения:  ('.date_fixed($new['downs_time']).')<br /><br />';

						echo '<div class="form">';
						echo '<form action="newload.php?act=edit&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'" method="post">';
						echo 'Категория*:<br />';

						$output = array();

						foreach ($downs as $row) {
							$i = $row['cats_id'];
							$p = $row['cats_parent'];
							$output[$p][$i] = $row;
						}

						echo '<select name="cid">';

						foreach ($output[0] as $key => $data) {
							$selected = ($new['downs_cats_id'] == $data['cats_id']) ? ' selected="selected"' : '';
							echo '<option value="'.$data['cats_id'].'"'.$selected.'>'.$data['cats_name'].'</option>';

							if (isset($output[$key])) {
								foreach($output[$key] as $datasub) {
									$selected = ($new['downs_cats_id'] == $datasub['cats_id']) ? ' selected="selected"' : '';
									echo '<option value="'.$datasub['cats_id'].'"'.$selected.'>– '.$datasub['cats_name'].'</option>';
								}
							}
						}

						echo '</select><br />';

						if (empty($new['downs_site'])) {
							$new['downs_site'] = 'http://';
						}

						$new['downs_text'] =yes_br($new['downs_text']);
						$new['downs_notice'] = yes_br($new['downs_notice']);

						echo 'Название*:<br />';
						echo '<input type="text" name="title" size="50" maxlength="50" value="'.$new['downs_title'].'" /><br />';
						echo 'Описание*:<br />';
						echo '<textarea cols="25" rows="10" name="text">'.$new['downs_text'].'</textarea><br />';
						echo 'Автор файла:<br />';
						echo '<input type="text" name="author" maxlength="50" value="'.$new['downs_author'].'" /><br />';
						echo 'Сайт автора:<br />';
						echo '<input type="text" name="site" maxlength="50" value="'.$new['downs_site'].'" /><br />';
						echo 'Имя файла*:<br />';
						echo '<input type="text" name="link" maxlength="50" value="'.$new['downs_link'].'" /><br />';
						echo 'Уведомление:<br />';
						echo '<textarea cols="25" rows="5" name="notice">'.$new['downs_notice'].'</textarea><br />';

						echo 'Файл проверен: ';
						$checked = ($new['downs_app'] == 1) ? ' checked="checked"' : '';
						echo '<input name="app" type="checkbox" value="1"'.$checked.' /><br /><br />';

						echo '<input value="Изменить" type="submit" /></form></div><br />';

					} else {
						show_error('Категории файлов еще не созданы!');
					}
				} else {
					show_error('Ошибка! Данный файл уже проверен модератором!');
				}
			} else {
				show_error('Данного файла не существует!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="newload.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                                   Редактирование                                       ##
		############################################################################################
		case 'edit':

			$uid = check($_GET['uid']);
			$cid = abs(intval($_POST['cid']));
			$title = check($_POST['title']);
			$text = check($_POST['text']);
			$author = check($_POST['author']);
			$site = ($_POST['site'] != 'http://') ? check($_POST['site']) : '';
			$link = check(strtolower($_POST['link']));
			$notice = check($_POST['notice']);
			$app = (empty($_POST['app'])) ? 0 : 1;

			if ($uid == $_SESSION['token']) {
				if (utf_strlen($title) >= 5 && utf_strlen($title) < 50) {
					if (utf_strlen($text) >= 10 && utf_strlen($text) < 5000) {
						if (utf_strlen($author) <= 50) {
							if (empty($site) || preg_match('#^http://([а-яa-z0-9_\-\.])+(\.([а-яa-z0-9\/])+)+$#u', $site)) {
								if (strlen($link) <= 50) {
									if (!preg_match('/\.(php|pl|cgi|phtml|htaccess)/i', $link)) {

										$new = DB::run() -> queryFetch("SELECT * FROM `downs` WHERE `downs_id`=?;", array($id));
										if (!empty($new)) {
											if (empty($new['downs_active'])) {
												$downs = DB::run() -> querySingle("SELECT `cats_id` FROM `cats` WHERE `cats_id`=? LIMIT 1;", array($cid));
												if (!empty($downs)) {
													$downlink = DB::run() -> querySingle("SELECT `downs_link` FROM `downs` WHERE `downs_link`=? AND `downs_id`<>? LIMIT 1;", array($link, $id));
													if (empty($downlink)) {

														$newtitle = DB::run() -> querySingle("SELECT `downs_title` FROM `downs` WHERE `downs_title`=? AND `downs_id`<>? LIMIT 1;", array($title, $id));
														if (empty($newtitle)) {

															$text = no_br($text);
															$notice = no_br($notice);

															if (!empty($link) && $link != $new['downs_link'] && file_exists(BASEDIR.'/load/files/'.$new['downs_link'])) {

																$oldext = getExtension($new['downs_link']);
																$newext = getExtension($link);

																if ($oldext == $newext) {

																	$screen = $new['downs_screen'];
																	rename(BASEDIR.'/load/files/'.$new['downs_link'], BASEDIR.'/load/files/'.$link);

																	if (!empty($new['downs_screen']) && file_exists(BASEDIR.'/load/screen/'.$new['downs_screen'])) {

																		$screen = $link.'.'.getExtension($new['downs_screen']);
																		rename(BASEDIR.'/load/screen/'.$new['downs_screen'], BASEDIR.'/load/screen/'.$screen);
																		unlink_image('load/screen/', $new['downs_screen']);
																	}
																	DB::run() -> query("UPDATE `downs` SET `downs_link`=?, `downs_screen`=? WHERE `downs_id`=?;", array($link, $screen, $id));
																}
															}

															if (!empty($notice) && $notice != $new['downs_notice']) {
																// ------------------------Уведомление по привату------------------------//
																if (check_user($new['downs_user'])) {
																	$textpriv = 'Уведомеление о проверке файла.<br />Ваш файл [b]'.$new['downs_title'].'[/b] не прошел проверку на добавление<br />Причина: '.$notice.'<br />Отредактировать описание файла вы можете на [url='.$config['home'].'/load/add.php?act=view&amp;id='.$id.']этой[/url] странице';

																	DB::run() -> query("INSERT INTO `inbox` (`inbox_user`, `inbox_author`, `inbox_text`, `inbox_time`) VALUES (?, ?, ?, ?);", array($new['downs_user'], $log, $textpriv, SITETIME));

																	DB::run() -> query("UPDATE `users` SET `users_newprivat`=`users_newprivat`+1 WHERE `users_login`=?", array($new['downs_user']));
																}
															}

															DB::run() -> query("UPDATE `downs` SET `downs_cats_id`=?, `downs_title`=?, `downs_text`=?, `downs_author`=?, `downs_site`=?, `downs_notice`=?, `downs_time`=?, `downs_app`=? WHERE `downs_id`=?;", array($cid, $title, $text, $author, $site, $notice, $new['downs_time'], $app, $id));

															$_SESSION['note'] = 'Данные успешно изменены!';
															redirect("newload.php?act=view&id=$id");

														} else {
															show_error('Ошибка! Название файла '.$title.' уже имеется в загрузках!');
														}
													} else {
														show_error('Ошибка! Имя файла '.$link.' уже имеется в загрузках!');
													}
												} else {
													show_error('Ошибка! Выбранный вами раздел не существует!');
												}
											} else {
												show_error('Ошибка! Данный файл уже проверен модератором!');
											}
										} else {
											show_error('Ошибка! Данного файла не существует!');
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
							show_error('Ошибка! Слишком длинный ник (логин) автора (не более 50 символов)!');
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

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="newload.php?act=view&amp;id='.$id.'">Вернуться</a><br />';
		break;

		############################################################################################
		##                                   Публикация файла                                     ##
		############################################################################################
		case 'allow':

			$uid = check($_GET['uid']);

			if (is_admin(array(101)) && $log == $config['nickname']) {
				if ($uid == $_SESSION['token']) {
					$new = DB::run() -> queryFetch("SELECT * FROM `downs` WHERE `downs_id`=? LIMIT 1;", array($id));

					if (!empty($new)) {
						if (empty($new['downs_active'])) {
							if (!empty($new['downs_link'])) {

								DB::run() -> query("UPDATE `downs` SET `downs_notice`=?, `downs_time`=?, `downs_app`=?, `downs_active`=? WHERE `downs_id`=?;", array('', SITETIME, 0, 1, $id));

								DB::run() -> query("UPDATE `cats` SET `cats_count`=`cats_count`+1 WHERE `cats_id`=?", array($new['downs_cats_id']));

								if (check_user($new['downs_user'])) {
									$textpriv = 'Уведомеление о проверке файла.<br />Ваш файл [b]'.$new['downs_title'].'[/b] успешно прошел проверку и добавлен в архив файлов<br />Просмотреть свой файл вы можете на [url='.$config['home'].'/load/down.php?act=view&amp;id='.$id.']этой[/url] странице';

									DB::run() -> query("INSERT INTO `inbox` (`inbox_user`, `inbox_author`, `inbox_text`, `inbox_time`) VALUES (?, ?, ?, ?);", array($new['downs_user'], $log, $textpriv, SITETIME));
									DB::run() -> query("UPDATE `users` SET `users_newprivat`=`users_newprivat`+1 WHERE `users_login`=?", array($new['downs_user']));
								}

								$_SESSION['note'] = 'Файл успешно опубликован!';
								redirect("newload.php");

							} else {
								show_error('Ошибка! В данной загрузке отсутствует прикрепленный файл!');
							}
						} else {
							show_error('Ошибка! Данный файл уже проверен модератором!');
						}
					} else {
						show_error('Ошибка! Данного файла не существует!');
					}
				} else {
					show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
				}
			} else {
				show_error('Ошибка! Опубликовывать файлы могут только суперадмины!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="newload.php?act=view&amp;id='.$id.'">Вернуться</a><br />';
		break;

		############################################################################################
		##                                   Удаление файла                                       ##
		############################################################################################
		case 'delfile':

			$link = DB::run() -> queryFetch("SELECT * FROM `downs` WHERE `downs_id`=?;", array($id));

			if (!empty($link)) {
				if (empty($link['downs_active'])) {

					if (!empty($link['downs_link']) && file_exists(BASEDIR.'/load/files/'.$link['downs_link'])) {
						unlink(BASEDIR.'/load/files/'.$link['downs_link']);
					}

					unlink_image('load/screen/', $link['downs_screen']);

					DB::run() -> query("UPDATE `downs` SET `downs_link`=?, `downs_screen`=? WHERE `downs_id`=?;", array('', '', $id));

					$_SESSION['note'] = 'Файл успешно удален!';
					redirect("newload.php?act=view&id=$id");

				} else {
					show_error('Ошибка! Данный файл уже проверен модератором!');
				}
			} else {
				show_error('Ошибка! Данного файла не существует!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="newload.php?act=view&amp;id='.$id.'">Вернуться</a><br />';
		break;

		############################################################################################
		##                                    Удаление скриншота                                  ##
		############################################################################################
		case 'delscreen':

			$screen = DB::run() -> queryFetch("SELECT `downs_screen` FROM `downs` WHERE `downs_id`=?;", array($id));
			if (!empty($screen)) {
				if (empty($screen['downs_active'])) {

					unlink_image('load/screen/', $screen['downs_screen']);

					DB::run() -> query("UPDATE `downs` SET `downs_screen`=? WHERE `downs_id`=?;", array('', $id));

					$_SESSION['note'] = 'Скриншот успешно удален!';
					redirect("newload.php?act=view&id=$id");

				} else {
					show_error('Ошибка! Данный файл уже проверен модератором!');
				}
			} else {
				show_error('Ошибка! Данного файла не существует!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="newload.php?act=view&amp;id='.$id.'">Вернуться</a><br />';
		break;

		############################################################################################
		##                                   Удаление файлов                                      ##
		############################################################################################
		case 'deldown':

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

					$querydel = DB::run() -> query("SELECT `downs_link`, `downs_screen` FROM `downs` WHERE `downs_id` IN (".$del.");");
					$arr_files = $querydel -> fetchAll();

					DB::run() -> query("DELETE FROM `downs` WHERE `downs_id` IN (".$del.");");

					foreach ($arr_files as $delfile) {

						if (!empty($delfile['downs_link']) && file_exists(BASEDIR.'/load/files/'.$delfile['downs_link'])) {
							unlink(BASEDIR.'/load/files/'.$delfile['downs_link']);
						}

						unlink_image('load/screen/', $delfile['downs_screen']);
					}

					$_SESSION['note'] = 'Выбранные файлы успешно удалены!';
					redirect("newload.php?start=$start");

				} else {
					show_error('Ошибка! Отсутствуют выбранные файлы!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="newload.php?start='.$start.'">Вернуться</a><br />';
		break;

	default:
		redirect("newload.php");
	endswitch;

	echo '<img src="/images/img/panel.gif" alt="image" /> <a href="index.php">В админку</a><br />';

} else {
	redirect('/index.php');
}

include_once ('../themes/footer.php');
?>
