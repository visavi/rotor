<?php
App::view($config['themes'].'/index');

$id = isset($_GET['id']) ? abs(intval($_GET['id'])) : 0;
$act = isset($_GET['act']) ? check($_GET['act']) : 'index';
$start = isset($_GET['start']) ? abs(intval($_GET['start'])) : 0;

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

                $zip = new PclZip('upload/files/'.$downs['downs_link']);
                if (($list = $zip -> listContent()) != 0) {
                    $intotal = $zip -> properties();
                    $total = $intotal['nb'];

                    sort($list);

                    if ($total > 0) {
                        echo '<i class="fa fa-archive"></i> <b>'.$downs['downs_title'].'</b><br /><br />';
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
                                echo '<i class="fa fa-folder-open-o"></i> <b>Директория '.$filename.'</b><br />';
                            } else {
                                $ext = getExtension($list[$i]['filename']);

                                echo icons($ext).' ';

                                if (in_array($ext, $arrext)) {
                                    echo '<a href="/load/zip?act=preview&amp;id='.$id.'&amp;view='.$list[$i]['index'].'&amp;start='.$start.'">'.$list[$i]['filename'].'</a>';
                                } else {
                                    echo $list[$i]['filename'];
                                }
                                echo ' ('.formatsize($list[$i]['size']).')<br />';
                            }
                        }

                        page_strnavigation('/load/zip?id='.$id.'&amp;', $config['ziplist'], $start, $total);

                        echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/down?cid='.$downs['cats_id'].'">'.$downs['cats_name'].'</a><br />';
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

    $view = isset($_GET['view']) ? abs(intval($_GET['view'])) : '';

    $downs = DB::run() -> queryFetch("SELECT * FROM `downs` WHERE `downs_id`=? LIMIT 1;", array($id));

    if (! empty($downs) && $view !== '') {
        if (!empty($downs['downs_active'])) {
            $zip = new PclZip('upload/files/'.$downs['downs_link']);

            $content = $zip -> extract(PCLZIP_OPT_BY_INDEX, $view, PCLZIP_OPT_EXTRACT_AS_STRING);
            if (!empty($content)) {
                $filecontent = $content[0]['content'];
                $filename = $content[0]['filename'];

                $config['newtitle'] = 'Просмотр файла - '.$filename;

                echo '<i class="fa fa-archive"></i> <b>'.$downs['downs_title'].'</b><br /><br />';

                echo '<b>'.$filename.'</b> ('.formatsize($content[0]['size']).')<hr />';

                if (!preg_match("/\.(gif|png|bmp|wbmp|jpg|jpeg)$/", $filename)) {
                    if ($content[0]['size'] > 0) {
                        if (is_utf($filecontent)) {
                            echo '<pre class="prettyprint linenums">'.htmlspecialchars($filecontent).'</pre><br />';
                        } else {
                            echo '<pre class="prettyprint linenums">'.win_to_utf(htmlspecialchars($filecontent)).'</pre><br />';
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
                        header("Content-Encoding: none");
                        header("Content-type: image/$ext");
                        header("Content-Length: ".strlen($filecontent));
                        header('Content-Disposition: inline; filename="'.$filename.'";');
                        die($filecontent);
                    }

                    echo '<img src="/load/zip?act=preview&amp;id='.$id.'&amp;view='.$view.'&amp;img=1" alt="image" /><br /><br />';
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

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/zip?id='.$id.'&amp;start='.$start.'">Вернуться</a><br />';
break;

endswitch;

echo '<i class="fa fa-arrow-circle-up"></i> <a href="/load">Категории</a><br />';

App::view($config['themes'].'/foot');
