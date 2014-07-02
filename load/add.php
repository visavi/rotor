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
if (isset($_GET['cid'])) {
	$cid = abs(intval($_GET['cid']));
} else {
	$cid = 0;
}
if (isset($_GET['id'])) {
	$id = abs(intval($_GET['id']));
} else {
	$id = 0;
}

if (is_user()) {
	if (is_admin() || $config['downupload'] == 1) {
		switch ($act):
		############################################################################################
		##                                   Главная страница                                     ##
		############################################################################################
			case 'index':

				show_title('Публикация нового файла');

				echo '<img src="/images/img/document.gif" alt="image" /> <b>Публикация</b> / ';
				echo '<a href="add.php?act=waiting">Ожидающие</a> / ';
				echo '<a href="active.php">Проверенные</a><hr />';

				if ($config['home'] == 'http://visavi.net') {
					echo '<div class="info">';
					echo '<img src="/images/img/faq.gif" alt="image" /> Перед публикацией скрипта настоятельно рекомендуем ознакомиться с <a href="add.php?act=rules&amp;cid='.$cid.'">правилами оформления скриптов</a><br />';
					echo 'Чем лучше вы оформите свой скрипт, тем быстрее он будет опубликован и добавлен в общий каталог</div><br />';
				}

				$querydown = DB::run() -> query("SELECT `cats_id`, `cats_parent`, `cats_name` FROM `cats` ORDER BY `cats_order` ASC;");
				$downs = $querydown -> fetchAll();

				if (count($downs) > 0) {
					echo '<div class="form">';
					echo '<form action="add.php?act=add&amp;uid='.$_SESSION['token'].'" method="post">';
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
			break;

			############################################################################################
			##                          Просмотр ожидающих модерации файлов                           ##
			############################################################################################
			case 'waiting':

				show_title('Список ожидающих модерации файлов');

				echo '<img src="/images/img/document.gif" alt="image" /> <a href="add.php">Публикация</a> / ';
				echo '<b>Ожидающие</b> / ';
				echo '<a href="active.php">Проверенные</a><hr />';

				$total = DB::run() -> querySingle("SELECT count(*) FROM `downs` WHERE `downs_active`=? AND `downs_user`=?;", array(0, $log));

				if ($total > 0) {
					$querynew = DB::run() -> query("SELECT `downs`.*, `cats_name` FROM `downs` LEFT JOIN `cats` ON `downs`.`downs_cats_id`=`cats`.`cats_id` WHERE `downs_active`=? AND `downs_user`=? ORDER BY `downs_time` DESC;", array(0, $log));

					while ($data = $querynew -> fetch()) {
						echo '<div class="b">';

						echo '<img src="/images/img/download.gif" alt="image" /> ';

						echo '<b><a href="add.php?act=view&amp;id='.$data['downs_id'].'">'.$data['downs_title'].'</a></b> ('.date_fixed($data['downs_time']).')</div>';
						echo '<div>';
						echo 'Категория: '.$data['cats_name'].'<br />';
						if (!empty($data['downs_link'])) {
							echo 'Файл: '.$data['downs_link'].' ('.read_file(BASEDIR.'/load/files/'.$data['downs_link']).')<br />';
						} else {
							echo 'Файл: <span style="color:#ff0000">Не загружен</span><br />';
						}
						if (!empty($data['downs_screen'])) {
							echo 'Скрин: '.$data['downs_screen'].' ('.read_file(BASEDIR.'/load/files/'.$data['downs_screen']).')<br />';
						} else {
							echo 'Скрин: <span style="color:#ff0000">Не загружен</span><br />';
						}
						echo '</div>';
					}

					echo '<br />';
				} else {
					show_error('Ожидающих модерации файлов еще нет!');
				}

				echo '<img src="/images/img/back.gif" alt="image" /> <a href="add.php">Вернуться</a><br />';
			break;

			############################################################################################
			##                                  Публикация файла                                      ##
			############################################################################################
			case 'add':

				show_title('Публикация нового файла');

				$uid = check($_GET['uid']);
				$cid = abs(intval($_POST['cid']));
				$title = check($_POST['title']);
				$text = check($_POST['text']);
				$author = (!empty($_POST['author'])) ? check($_POST['author']) : '';
				$site = ($_POST['site'] != 'http://') ? check($_POST['site']) : '';

				if ($uid == $_SESSION['token']) {
					if (!empty($cid)) {
						if (utf_strlen($title) >= 5 && utf_strlen($title) <= 50) {
							if (utf_strlen($text) >= 50 && utf_strlen($text) <= 5000) {
								if (utf_strlen($author) <= 50) {
									if (utf_strlen($site) <= 50) {
										if (empty($site) || preg_match('#^http://([а-яa-z0-9_\-\.])+(\.([а-яa-z0-9\/])+)+$#u', $site)) {
											$downs = DB::run() -> querySingle("SELECT `cats_id` FROM `cats` WHERE `cats_id`=? LIMIT 1;", array($cid));
											if (!empty($downs)) {
												$downtitle = DB::run() -> querySingle("SELECT `downs_title` FROM `downs` WHERE `downs_title`=? LIMIT 1;", array($title));
												if (empty($downtitle)) {
													$text = no_br($text);

													//DB::run() -> query("UPDATE `cats` SET `cats_count`=`cats_count`+1 WHERE `cats_id`=?", array($cid));
													DB::run() -> query("INSERT INTO `downs` (`downs_cats_id`, `downs_title`, `downs_text`, `downs_link`, `downs_user`, `downs_author`, `downs_site`, `downs_screen`, `downs_time`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?);", array($cid, $title, $text, '', $log, $author, $site, '', SITETIME));

													$lastid = DB::run() -> lastInsertId();

													$_SESSION['note'] = 'Данные успешно добавлены!';
													redirect("add.php?act=view&id=$lastid");

												} else {
													show_error('Ошибка! Название файла '.$title.' уже имеется в загрузках!');
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
									show_error('Ошибка! Слишком длинный ник (логин) автора (до 50 символов)!');
								}
							} else {
								show_error('Ошибка! Слишком длинный или короткий текст описания (от 50 до 5000 символов)!');
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

				echo '<img src="/images/img/back.gif" alt="image" /> <a href="add.php?act=newfile&amp;cid='.$cid.'">Вернуться</a><br />';
			break;

			############################################################################################
			##                            Подготовка к редактированию файла                           ##
			############################################################################################
			case 'view':

				show_title('Редактирование ожидающего файла');

				echo '<img src="/images/img/document.gif" alt="image" /> <a href="add.php">Публикация</a> / ';
				echo '<b><a href="add.php?act=waiting">Ожидающие</a></b> / ';
				echo '<a href="active.php?act=files">Проверенные</a><hr />';

				$new = DB::run() -> queryFetch("SELECT `downs`.*, `cats`.* FROM `downs` LEFT JOIN `cats` ON `downs`.`downs_cats_id`=`cats`.`cats_id` WHERE `downs_id`=? LIMIT 1;", array($id));

				if (!empty($new)) {
					$downs = DB::run() -> query("SELECT `cats_id`, `cats_parent`, `cats_name` FROM `cats` ORDER BY `cats_order` ASC;") -> fetchAll();
					if (count($downs) > 0) {
						if ($new['downs_user'] == $log) {
							if (empty($new['downs_active'])) {

								echo '<a href="#down"><img src="/images/img/downs.gif" alt="Вниз" /></a> <a href="index.php">Категории</a> / ';

								if (!empty($new['cats_parent'])) {
									$podcats = DB::run() -> queryFetch("SELECT `cats_id`, `cats_name` FROM `cats` WHERE `cats_id`=? LIMIT 1;", array($new['cats_parent']));
									echo '<a href="down.php?cid='.$podcats['cats_id'].'">'.$podcats['cats_name'].'</a> / ';
								}

								echo '<a href="down.php?act=view&amp;id='.$id.'">Обзор файла</a><br /><br />';

								echo '<div class="info"><b>Внимание!</b> Данная загрузка опубликована, но еще требует модераторской проверки<br />После проверки вы не сможете отредактировать описание и загрузить файл или скриншот</div><br />';

								if (empty($new['downs_link'])) {

									echo '<b><big>Загрузка файла</big></b><br /><br />';
									echo '<div class="info">';
									echo '<form action="add.php?act=loadfile&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'" method="post" enctype="multipart/form-data">';
									echo 'Прикрепить файл* ('.$config['allowextload'].'):<br /><input type="file" name="loadfile" /><br />';
									echo '<input value="Загрузить" type="submit" /></form></div><br />';

								} else {

									echo '<img src="/images/img/download.gif" alt="image" /> <b><a href="/load/files/'.$new['downs_link'].'">'.$new['downs_link'].'</a></b> ('.read_file(BASEDIR.'/load/files/'.$new['downs_link']).') (<a href="add.php?act=delfile&amp;id='.$id.'" onclick="return confirm(\'Вы действительно хотите удалить данный файл?\')">Удалить</a>)<br />';

									$ext = getExtension($new['downs_link']);
									if ($ext != 'jpg' && $ext != 'jpeg' && $ext != 'gif' && $ext != 'png') {
										if (empty($new['downs_screen'])) {
											echo '<br /><b><big>Загрузка скриншота</big></b><br /><br />';
											echo '<div class="info">';
											echo '<form action="add.php?act=loadscreen&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'" method="post" enctype="multipart/form-data">';
											echo 'Прикрепить скрин (jpg,jpeg,gif,png):<br /><input type="file" name="screen" /><br />';
											echo '<input value="Загрузить" type="submit" /></form></div><br />';
										} else {
											echo '<img src="/images/img/gallery.gif" alt="image" /> <b><a href="/load/screen/'.$new['downs_screen'].'">'.$new['downs_screen'].'</a></b> ('.read_file(BASEDIR.'/load/screen/'.$new['downs_screen']).') (<a href="add.php?act=delscreen&amp;id='.$id.'" onclick="return confirm(\'Вы действительно хотите удалить данный скриншот?\')">Удалить</a>)<br /><br />';
											echo resize_image('load/screen/', $new['downs_screen'], $config['previewsize']).'<br />';
										}
									}
								}

								echo '<br />';
								echo '<b><big>Редактирование</big></b><br /><br />';
								echo '<div class="form">';
								echo '<form action="add.php?act=edit&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'" method="post">';

								echo 'Категория*:<br />';

								$output = array();

								foreach ($downs as $row) {
									$i = $row['cats_id'];
									$p = $row['cats_parent'];
									$output[$p][$i] = $row;
								}

								echo '<select name="cid">';

								foreach ($output[0] as $key => $data) {
									$selected = ($new['cats_id'] == $data['cats_id']) ? ' selected="selected"' : '';
									echo '<option value="'.$data['cats_id'].'"'.$selected.'>'.$data['cats_name'].'</option>';

									if (isset($output[$key])) {
										foreach($output[$key] as $datasub) {
											$selected = ($new['cats_id'] == $datasub['cats_id']) ? ' selected="selected"' : '';
											echo '<option value="'.$datasub['cats_id'].'"'.$selected.'>– '.$datasub['cats_name'].'</option>';
										}
									}
								}

								echo '</select><br />';

								$new['downs_text'] = yes_br(nosmiles($new['downs_text']));

								echo 'Название*:<br />';
								echo '<input type="text" name="title" size="50" maxlength="50" value="'.$new['downs_title'].'" /><br />';
								echo 'Описание*:<br />';
								echo '<textarea cols="25" rows="5" name="text">'.$new['downs_text'].'</textarea><br />';
								echo 'Автор файла:<br />';
								echo '<input type="text" name="author" maxlength="50" value="'.$new['downs_author'].'" /><br />';
								echo 'Сайт автора:<br />';
								echo '<input type="text" name="site" maxlength="50" value="'.$new['downs_site'].'" /><br />';

								echo '<input value="Изменить" type="submit" /></form></div><br />';
								echo 'Все поля отмеченные знаком *, обязательны для заполнения<br /><br />';

							} else {
								show_error('Ошибка! Данный файл уже проверен модератором!');
							}
						} else {
							show_error('Ошибка! Изменение невозможно, вы не автор данного файла!');
						}
					} else {
						show_error('Категории файлов еще не созданы!');
					}
				} else {
					show_error('Данного файла не существует!');
				}

			break;

			############################################################################################
			##                                  Редактирование файла                                  ##
			############################################################################################
			case 'edit':

				show_title('Редактирование ожидающего файла');

				$uid = check($_GET['uid']);
				$cid = abs(intval($_POST['cid']));
				$title = check($_POST['title']);
				$text = check($_POST['text']);
				$author = (!empty($_POST['author'])) ? check($_POST['author']) : '';
				$site = ($_POST['site'] != 'http://') ? check($_POST['site']) : '';

				if ($uid == $_SESSION['token']) {
					if (utf_strlen($title) >= 5 && utf_strlen($title) <= 50) {
						if (utf_strlen($text) >= 50 && utf_strlen($text) <= 5000) {
							if (utf_strlen($author) <= 50) {
								if (utf_strlen($site) <= 50) {
									if (empty($site) || preg_match('#^http://([а-яa-z0-9_\-\.])+(\.([а-яa-z0-9\/])+)+$#u', $site)) {
										$new = DB::run() -> queryFetch("SELECT * FROM `downs` WHERE `downs_id`=? LIMIT 1;", array($id));
										if (!empty($new)) {
											if ($new['downs_user'] == $log) {
												if (empty($new['downs_active'])) {

													$categories = DB::run() -> querySingle("SELECT `cats_id` FROM `cats` WHERE `cats_id`=? LIMIT 1;", array($cid));
													if (!empty($categories)) {

														$newtitle = DB::run() -> querySingle("SELECT `downs_title` FROM `downs` WHERE `downs_title`=? AND `downs_id`<>? LIMIT 1;", array($title, $id));
														if (empty($newtitle)) {

															$text = no_br($text);

															DB::run() -> query("UPDATE `downs` SET `downs_cats_id`=?, `downs_title`=?, `downs_text`=?, `downs_author`=?, `downs_site`=?, `downs_time`=? WHERE `downs_id`=?;", array($cid, $title, $text, $author, $site, $new['downs_time'], $id));

															$_SESSION['note'] = 'Данные успешно изменены!';
															redirect("add.php?act=view&id=$id");

														} else {
															show_error('Ошибка! Название файла '.$title.' уже имеется в загрузках!');
														}
													} else {
														show_error('Ошибка! Выбранный вами раздел не существует!');
													}
												} else {
													show_error('Ошибка! Данный файл уже проверен модератором!');
												}
											} else {
												show_error('Ошибка! Изменение невозможно, вы не автор данного файла!');
											}
										} else {
											show_error('Данного файла не существует!');
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
							show_error('Ошибка! Слишком длинный или короткий текст описания (от 50 до 5000 символов)!');
						}
					} else {
						show_error('Ошибка! Слишком длинное или короткое название (от 5 до 50 символов)!');
					}
				} else {
					show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
				}

				echo '<img src="/images/img/back.gif" alt="image" /> <a href="add.php?act=view&amp;id='.$id.'">Вернуться</a><br />';
			break;

			############################################################################################
			##                                   Загрузка файла                                       ##
			############################################################################################
			case 'loadfile':
				show_title('Загрузка файла');

				$down = DB::run() -> queryFetch("SELECT * FROM `downs` WHERE `downs_id`=? LIMIT 1;", array($id));
				if (!empty($down)) {
					if ($down['downs_user'] == $log) {
						if (empty($down['downs_active'])) {
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
															redirect("add.php?act=view&id=$id");

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
							show_error('Ошибка! Данный файл уже проверен модератором!');
						}
					} else {
						show_error('Ошибка! Изменение невозможно, вы не автор данного файла!');
					}
				} else {
					show_error('Данного файла не существует!');
				}

				echo '<img src="/images/img/back.gif" alt="image" /> <a href="add.php?act=view&amp;id='.$id.'">Вернуться</a><br />';
			break;

			############################################################################################
			##                                   Загрузка скриншота                                   ##
			############################################################################################
			case 'loadscreen':
				show_title('Загрузка скриншота');

				$down = DB::run() -> queryFetch("SELECT * FROM `downs` WHERE `downs_id`=? LIMIT 1;", array($id));
				if (!empty($down)) {
					if ($down['downs_user'] == $log) {
						if (empty($down['downs_active'])) {
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
															redirect("add.php?act=view&id=$id");

														} else {
															show_error('Ошибка! '.$handle -> error);
														}
													} else {
														show_error('Ошибка! Не удалось загрузить изображение!');
													}

												} else {
													show_error('Ошибка! Требуемый размер скриншота: от 100 до '.$config['screensize'].' px');
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
							show_error('Ошибка! Данный файл уже проверен модератором!');
						}
					} else {
						show_error('Ошибка! Изменение невозможно, вы не автор данного файла!');
					}
				} else {
					show_error('Данного файла не существует!');
				}

				echo '<img src="/images/img/back.gif" alt="image" /> <a href="add.php?act=view&amp;id='.$id.'">Вернуться</a><br />';
			break;

			############################################################################################
			##                                   Удаление файла                                       ##
			############################################################################################
			case 'delfile':

				$link = DB::run() -> queryFetch("SELECT * FROM `downs` WHERE `downs_id`=? LIMIT 1;", array($id));
				if (!empty($link)) {
					if ($link['downs_user'] == $log) {
						if (empty($link['downs_active'])) {

							if (!empty($link['downs_link']) && file_exists(BASEDIR.'/load/files/'.$link['downs_link'])) {
								unlink(BASEDIR.'/load/files/'.$link['downs_link']);
							}

							unlink_image('load/screen/', $link['downs_screen']);

							DB::run() -> query("UPDATE `downs` SET `downs_link`=?, `downs_screen`=? WHERE `downs_id`=?;", array('', '', $id));

							$_SESSION['note'] = 'Файл успешно удален!';
							redirect("add.php?act=view&id=$id");

						} else {
							show_error('Ошибка! Данный файл уже проверен модератором!');
						}
					} else {
						show_error('Ошибка! Удаление невозможно, вы не автор данного файла!');
					}
				} else {
					show_error('Ошибка! Данного файла не существует!');
				}

				echo '<img src="/images/img/back.gif" alt="image" /> <a href="add.php?act=view&amp;id='.$id.'">Вернуться</a><br />';
			break;

			############################################################################################
			##                                    Удаление скриншота                                  ##
			############################################################################################
			case 'delscreen':

				$screen = DB::run() -> queryFetch("SELECT * FROM `downs` WHERE `downs_id`=? LIMIT 1;", array($id));
				if (!empty($screen)) {
					if ($screen['downs_user'] == $log) {
						if (empty($screen['downs_active'])) {

							unlink_image('load/screen/', $screen['downs_screen']);

							DB::run() -> query("UPDATE `downs` SET `downs_screen`=? WHERE `downs_id`=?;", array('', $id));

							$_SESSION['note'] = 'Скриншот успешно удален!';
							redirect("add.php?act=view&id=$id");

						} else {
							show_error('Ошибка! Данный файл уже проверен модератором!');
						}
					} else {
						show_error('Ошибка! Удаление невозможно, вы не автор данного файла!');
					}
				} else {
					show_error('Ошибка! Данного файла не существует!');
				}

				echo '<img src="/images/img/back.gif" alt="image" /> <a href="add.php?act=view&amp;id='.$id.'">Вернуться</a><br />';
			break;

			############################################################################################
			##                                      Правила                                           ##
			############################################################################################
			case 'rules':
				if ($config['home'] == 'http://visavi.net') {

					show_title('Правила оформления скриптов');

					echo '<b><span style="color:#ff0000">Внимание! Запрещено выкладывать платные скрипты или скрипты не предназначенные для свободного распространения.<br />
	 Запрещено размещать скрипты накрутчиков, скрипты для спама, взлома или любые вредоносные скрипты</span></b><br /><br />';

					echo 'Чтобы не превращать архив скриптов в свалку мусора, все скрипты на нашем сайте проходят ручную обработку<br />';
					echo 'Если вы хотите добавить скрипт, НЕ обязательно быть его автором, но вы обязательно должны указать данные и контакты автора скрипта<br />';
					echo 'Также если вы автор модификации или небольшой переделки, то можете указать и свои данные в описании к скрипту<br /><br />';

					echo '<b>Авторские права</b><br />';
					echo 'Для размещения скрипта в нашем архиве вы должны быть автором этого скрипта, у вас должны быть эсклюзивные права для размещения этого скрипта или лицензия на распространения скрипта<br />';
					echo 'Не рекомендуется публиковать скрипт если вы не уверены, что он распространяется свободно или автор не против этого<br />';
					echo 'Все скрипты размещенные у нас не могут быть удалены с нашего сайта, исключением является публикация скрипта без согласия автора или выложенных с нарушением текущих правил и только по требованию автора<br />';
					echo 'Размещая скрипт у нас вы автоматически соглашаетесь со всеми правилами<br /><br />';

					echo '<b>В архиве со скриптом должны быть следующие, обязательные файлы:</b><br />';

					echo '<b>1.</b> Сам скрипт. Все файлы необходимые для нормальной работы<br />';
					echo '<b>2.</b> Инструкция по установке. Как правильно установить скрипт<br />';
					echo '<b>3.</b> Полное описание скрипта. Какие функции имеются в этом скрипте, возможности и т.д.<br />';
					echo '<b>4.</b> Требования для работы. (К примеру PHP4, HTML, библиотека ICONV)<br />';
					echo '<b>5.</b> Автор скрипта и(или) автор модификации<br />';
					echo '<b>6.</b> Контакты авторов (адрес сайта)<br />';
					echo '<b>7.</b> Красивое и уникальное название архива и скрипта<br /><br />';

					echo '<b>Примеры описания скриптов</b><br />';
					echo 'Название: <b>cat_skor</b><br />';
					echo 'Каталог мобильных сайтов в трех версиях: wml xhtml и html.<br />
	Возможности<br />
	- Полная статистика каталога: переходы по дням, по месяцам и за все время.<br />
	- Полная статистика по каждому сайту: переходы по дням, месяцам, переходы за все время, описание.<br />
	- Автоудаление неактивных сайтов.<br />
	- Отчет на email за каждый день ....... (и т.д.)
	<br />
	Требования: PHP4, MySQL, WML, (X)HTML, CRON<br />
	Автор cкрипта: skor<br />
	Сайт автора http://xwap.org<br /><br />';

					echo '<b>Ограничения:</b><br />';
					echo 'К загрузке допускаются архивы в формате zip, скриншоты можно загружать в форматах jpg, jpeg, gif и png<br />';
					echo 'Максимальный вес архива: '.formatsize($config['fileupload']).'<br />';
					echo 'Максимальный вес скриншота: '.formatsize($config['screenupload']).'<br />';
					echo 'Требуемый размер скриншота: от 100 до '.$config['screenupsize'].' px<br /><br />';

					echo '<b>Рекомендации:</b><br />';
					echo 'Чем лучше вы оформите скрипт при публикации, тем быстрее он будет проверен и размещен в архиве<br />';
					echo 'Рекомендуем самостоятельно подготовить хорошее и граммотное описание скрипта, а не просто скопировать и вставить текст<br />';
					echo 'Важным моментом является выбор названия и имени архива со скриптом, они должны быть уникальными, нельзя добавлять к примеру gb.zip, forum.zip и т.д. так как эти названия не уникальные и подходят под большинство скриптов выбранной категории<br />';
					echo 'Название и имя архива не должны быть слишком короткими или длинными, не должны быть чересчур информативными<br /><br />';

					echo 'После проверки ваш скрипт будет размещен в нашем архиве и станет доступным для скачивания, добавления оценок и комментариев<br /><br />';

					echo '<img src="/images/img/back.gif" alt="image" /> <a href="add.php?cid='.$cid.'">Вернуться</a><br />';
				}
			break;
			default:
				redirect("add.php");
			endswitch;

		} else {
			show_error('Возможность добавление файлов запрещена администрацией сайта');
		}
	} else {
		show_login('Вы не авторизованы, для добавления файла, необходимо');
	}

echo '<img src="/images/img/reload.gif" alt="image" /> <a href="index.php">Категории</a><br />';

include_once ('../themes/footer.php');
?>
