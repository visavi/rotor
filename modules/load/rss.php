<?php
App::view($config['themes'].'/index');

$id = isset($_GET['id']) ? abs(intval($_GET['id'])) : 0;

show_title('Комментарии');

$down = DB::run() -> queryFetch("SELECT * FROM `downs` WHERE `downs_id`=? LIMIT 1;", array($id));

if (!empty($down)) {
    if (!empty($down['downs_active'])) {
        while (ob_get_level()) {
            ob_end_clean();
        }
        header("Content-Encoding: none");
        header("Content-type:application/rss+xml; charset=utf-8");
        echo '<?xml version="1.0" encoding="utf-8"?>';
        echo '<rss version="2.0"><channel>';
        echo '<title>Комментарии - '.$down['downs_title'].'</title>';
        echo '<link>'.$config['home'].'</link>';
        echo '<description>Комментарии RSS - '.$config['title'].'</description>';
        echo '<image><url>'.$config['logotip'].'</url>';
        echo '<title>Комментарии - '.$down['downs_title'].'</title>';
        echo '<link>'.$config['home'].'</link></image>';
        echo '<language>ru</language>';
        echo '<copyright>'.$config['copy'].'</copyright>';
        echo '<managingEditor>'.$config['emails'].'</managingEditor>';
        echo '<webMaster>'.$config['emails'].'</webMaster>';
        echo '<lastBuildDate>'.date("r", SITETIME).'</lastBuildDate>';

        $querycomm = DB::run() -> query("SELECT * FROM `commload` WHERE `commload_down`=? ORDER BY `commload_time` DESC LIMIT 15;", array($id));

        while ($data = $querycomm -> fetch()) {
            $data['commload_text'] = bb_code($data['commload_text']);
            $data['commload_text'] = str_replace('/images/smiles', $config['home'].'/images/smiles', $data['commload_text']);
            $data['commload_text'] = htmlspecialchars($data['commload_text']);

            echo '<item><title>'.$down['downs_title'].'</title><link>'.$config['home'].'/load/down?act=comments&amp;id='.$down['downs_id'].'</link>';
            echo '<description>'.$data['commload_text'].' </description><author>'.nickname($data['commload_author']).'</author>';
            echo '<pubDate>'.date("r", $data['commload_time']).'</pubDate><category>Комментарии</category><guid>'.$config['home'].'/load/down?act=comments&amp;id='.$down['downs_id'].'&amp;pid='.$data['commload_id'].'</guid></item>';
        }

        echo '</channel></rss>';
        exit;
    } else {
        show_error('Ошибка! Данный файл еще не проверен модератором!');
    }
} else {
    show_error('Ошибка! Выбранный вами файл не существует, возможно он был удален!');
}

echo '<i class="fa fa-arrow-circle-up"></i> <a href="/load">Категории</a>';

App::view($config['themes'].'/foot');
