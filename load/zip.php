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

if (isset($_GET['id'])) {
	$id = abs(intval($_GET['id']));
} else {
	$id = 0;
}
if (isset($_GET['start'])) {
	$start = abs(intval($_GET['start']));
} else {
	$start = 0;
}
if (isset($_GET['act'])) {
	$act = check($_GET['act']);
} else {
	$act = 'index';
}

show_title('Просмотр архива');

switch ($act):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
	case 'index':
		$downs = DB::run() -> queryFetch("SELECT `downs`.*, `cats`.`cats_id`, `cats`.`cats_name` FROM `downs` LEFT JOIN `cats` ON `downs`.`downs_cats_id`=`cats`.`cats_id` WHERE `downs_id`=? LIMIT 1;", array($id));

		if (!empty($downs)) {
			if (!empty($downs['downs_active'])) {
				if (getExtension($downs['downs_link']) == 'zip') {
					$config['newtitle'] = 'Просмотр архива - '.$downs['downs_title'];

					$zip = new PclZip('files/'.$downs['downs_link']);
					if (($list = $zip -> listContent()) != 0) {
						$intotal = $zip -> properties();
						$total = $intotal['nb'];

						sort($list);

						if ($total > 0) {
							echo '<img src="/images/img/zip.gif" alt="image" /> <b>'.$downs['downs_title'].'</b><br /><br />';
							echo 'Всего файлов: '.$total.'<hr />';

							$arrext = array('xml', 'wml', 'asp', 'aspx', 'shtml', 'htm', 'phtml', 'html', 'php', 'htt', 'dat', 'tpl', 'htaccess', 'pl', 'js', 'jsp', 'css', 'txt', 'sql', 'gif', 'png', 'bmp', 'wbmp', 'jpg', 'jpeg');

							if ($start < 0 || $start >= $total) {
								$start = 0;
							}
							if ($total < $start + $config['ziplist']) {
								$end = $total;
							} else {
								$end = $start + $config['ziplist'];
							}
							for ($i = $start; $i < $end; $i++) {
								if ($list[$i]['folder'] == 1) {
									$filename = substr($list[$i]['filename'], 0, -1);
									echo '<img src="/images/icons/dir.gif" alt="image" /> <b>Директория '.$filename.'</b><br />';
								} else {
									$ext = getExtension($list[$i]['filename']);

									echo '<img src="/images/icons/'.icons($ext).'" alt="image" /> ';

									if (in_array($ext, $arrext)) {
										echo '<a href="zip.php?act=preview&amp;id='.$id.'&amp;view='.$list[$i]['index'].'&amp;start='.$start.'">'.$list[$i]['filename'].'</a>';
									} else {
										echo $list[$i]['filename'];
									}
									echo ' ('.formatsize($list[$i]['size']).')<br />';
								}
							}

							page_strnavigation('zip.php?id='.$id.'&amp;', $config['ziplist'], $start, $total);

							echo '<img src="/images/img/back.gif" alt="image" /> <a href="down.php?cid='.$downs['cats_id'].'">'.$downs['cats_name'].'</a><br />';
						} else {
							show_error('Ошибка! В данном архиве нет файлов!');
						}
					} else {
						show_error('Ошибка! Невозможно открыть архив!');
					}
				} else {
					show_error('Ошибка! Невозможно просмотреть данный файл, т.к. он не является архивом!');
				}
			} else {
				show_error('Ошибка! Данный файл еще не проверен модератором!');
			}
		} else {
			show_error('Ошибка! Данного файла не существует!');
		}
	break;

	############################################################################################
	##                                    Просмотр файла                                      ##
	############################################################################################
	case 'preview':

		$view = abs(intval($_GET['view']));

		$downs = DB::run() -> queryFetch("SELECT * FROM `downs` WHERE `downs_id`=? LIMIT 1;", array($id));

		if (!empty($downs)) {
			if (!empty($downs['downs_active'])) {
				$zip = new PclZip('files/'.$downs['downs_link']);

				$content = $zip -> extract(PCLZIP_OPT_BY_INDEX, $view, PCLZIP_OPT_EXTRACT_AS_STRING);
				if (!empty($content)) {
					$filecontent = $content[0]['content'];
					$filename = $content[0]['filename'];

					$config['newtitle'] = 'Просмотр файла - '.$filename;

					echo '<img src="/images/img/zip.gif" alt="image" /> <b>'.$downs['downs_title'].'</b><br /><br />';

					echo '<b>'.$filename.'</b> ('.formatsize($content[0]['size']).')<hr />';

					if (!preg_match("/\.(gif|png|bmp|wbmp|jpg|jpeg)$/", $filename)) {
						if ($content[0]['size'] > 0) {
							if (is_utf($filecontent)) {
								echo '<div class="d">'.highlight_string($filecontent, 1).'</div><br />';
							} else {
								echo '<div class="d">'.highlight_string(win_to_utf($filecontent), 1).'</div><br />';
							}
						} else {
							show_error('Данный файл пустой!');
						}
					} else {
						if (!empty($_GET['img'])) {
							$ext = getExtension($filename);

							while (ob_get_level()) {
								ob_end_clean();
							}
							header($_SERVER["SERVER_PROTOCOL"].' 200 OK');
							header("Content-type: image/$ext");
							header("Content-Length: ".strlen($filecontent));
							header('Connection: close');
							header('Content-Disposition: inline; filename="'.$filename.'";');
							die($filecontent);
						}

						echo '<img src="zip.php?act=preview&amp;id='.$id.'&amp;view='.$view.'&amp;img=1" alt="image" /><br /><br />';
					}
				} else {
					show_error('Ошибка! Не удалось извлечь файл!');
				}
			} else {
				show_error('Ошибка! Данный файл еще не проверен модератором!');
			}
		} else {
			show_error('Ошибка! Данного файла не существует!');
		}

		echo '<img src="/images/img/back.gif" alt="image" /> <a href="zip.php?id='.$id.'&amp;start='.$start.'">Вернуться</a><br />';
	break;

default:
	redirect("zip.php");
endswitch;

echo '<img src="/images/img/reload.gif" alt="image" /> <a href="index.php">Категории</a><br />';

include_once ('../themes/footer.php');
?>
