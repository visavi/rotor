<?php
view(setting('themes').'/index');

$id = isset($_GET['id']) ? abs(intval($_GET['id'])) : 0;
$act = isset($_GET['act']) ? check($_GET['act']) : 'index';
$page = abs(intval(Request::input('page', 1)));

//show_title('Просмотр архива');

switch ($action):


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

                //setting('newtitle') = 'Просмотр файла - '.$filename;

                echo '<i class="fa fa-archive"></i> <b>'.$downs['title'].'</b><br><br>';

                echo '<b>'.$filename.'</b> ('.formatSize($content[0]['size']).')<hr>';

                if (!preg_match("/\.(gif|png|bmp|jpg|jpeg)$/", $filename)) {
                    if ($content[0]['size'] > 0) {
                        if (isUtf($filecontent)) {
                            echo '<pre class="prettyprint linenums">'.htmlspecialchars($filecontent).'</pre><br>';
                        } else {
                            echo '<pre class="prettyprint linenums">'.winToUtf(htmlspecialchars($filecontent)).'</pre><br>';
                        }
                    } else {
                        showError('Данный файл пустой!');
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

                    echo '<img src="/load/zip?act=preview&amp;id='.$id.'&amp;view='.$view.'&amp;img=1" alt="image"><br><br>';
                }
            } else {
                showError('Ошибка! Не удалось извлечь файл!');
            }
        } else {
            showError('Ошибка! Данный файл еще не проверен модератором!');
        }
    } else {
        showError('Ошибка! Данного файла не существует!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/zip?id='.$id.'&amp;page='.$page.'">Вернуться</a><br>';
break;

endswitch;

echo '<i class="fa fa-arrow-circle-up"></i> <a href="/load">Категории</a><br>';

view(setting('themes').'/foot');
