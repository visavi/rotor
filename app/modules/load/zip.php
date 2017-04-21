<?php
App::view(App::setting('themes').'/index');

$id = isset($_GET['id']) ? abs(intval($_GET['id'])) : 0;
$act = isset($_GET['act']) ? check($_GET['act']) : 'index';
$page = abs(intval(Request::input('page', 1)));

//show_title('Просмотр архива');

switch ($act):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
case 'index':
    $downs = DB::run() -> queryFetch("SELECT `d`.*, `c`.`name` FROM `downs` d LEFT JOIN `cats` c ON `d`.`category_id`=`c`.`id` WHERE d.`id`=? LIMIT 1;", [$id]);

    if (!empty($downs)) {
        if (!empty($downs['active'])) {
            if (getExtension($downs['link']) == 'zip') {
                //App::setting('newtitle') = 'Просмотр архива - '.$downs['title'];

                $zip = new PclZip('uploads/files/'.$downs['link']);
                if (($list = $zip -> listContent()) != 0) {
                    $intotal = $zip -> properties();
                    $total = $intotal['nb'];

                    sort($list);

                    $page = App::paginate(App::setting('ziplist'), $total);
                    if ($total > 0) {
                        echo '<i class="fa fa-archive"></i> <b>'.$downs['title'].'</b><br /><br />';
                        echo 'Всего файлов: '.$total.'<hr />';

                        $arrext = ['xml', 'wml', 'asp', 'aspx', 'shtml', 'htm', 'phtml', 'html', 'php', 'htt', 'dat', 'tpl', 'htaccess', 'pl', 'js', 'jsp', 'css', 'txt', 'sql', 'gif', 'png', 'bmp', 'wbmp', 'jpg', 'jpeg', 'env', 'gitignore', 'json', 'yml', 'md'];

                        if ($total < $page['offset'] + App::setting('ziplist')) {
                            $end = $total;
                        } else {
                            $end = $page['offset'] + App::setting('ziplist');
                        }
                        for ($i = $page['offset']; $i < $end; $i++) {
                            if ($list[$i]['folder'] == 1) {
                                $filename = substr($list[$i]['filename'], 0, -1);
                                echo '<i class="fa fa-folder-open-o"></i> <b>Директория '.$filename.'</b><br />';
                            } else {
                                $ext = getExtension($list[$i]['filename']);

                                echo icons($ext).' ';

                                if (in_array($ext, $arrext)) {
                                    echo '<a href="/load/zip?act=preview&amp;id='.$id.'&amp;view='.$list[$i]['index'].'&amp;page='.$page['current'].'">'.$list[$i]['filename'].'</a>';
                                } else {
                                    echo $list[$i]['filename'];
                                }
                                echo ' ('.formatsize($list[$i]['size']).')<br />';
                            }
                        }

                        App::pagination($page);

                        echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/down?cid='.$downs['category_id'].'">'.$downs['name'].'</a><br />';
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

    $downs = DB::run() -> queryFetch("SELECT * FROM `downs` WHERE `id`=? LIMIT 1;", [$id]);

    if (! empty($downs) && $view !== '') {
        if (!empty($downs['active'])) {
            $zip = new PclZip('uploads/files/'.$downs['link']);

            $content = $zip -> extract(PCLZIP_OPT_BY_INDEX, $view, PCLZIP_OPT_EXTRACT_AS_STRING);
            if (!empty($content)) {
                $filecontent = $content[0]['content'];
                $filename = $content[0]['filename'];

                //App::setting('newtitle') = 'Просмотр файла - '.$filename;

                echo '<i class="fa fa-archive"></i> <b>'.$downs['title'].'</b><br /><br />';

                echo '<b>'.$filename.'</b> ('.formatsize($content[0]['size']).')<hr />';

                if (!preg_match("/\.(gif|png|bmp|jpg|jpeg)$/", $filename)) {
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

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/zip?id='.$id.'&amp;page='.$page.'">Вернуться</a><br />';
break;

endswitch;

echo '<i class="fa fa-arrow-circle-up"></i> <a href="/load">Категории</a><br />';

App::view(App::setting('themes').'/foot');
