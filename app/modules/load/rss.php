<?php
App::view($config['themes'].'/index');

$id = isset($_GET['id']) ? abs(intval($_GET['id'])) : 0;

show_title('Комментарии');

$down = DB::run() -> queryFetch("SELECT * FROM `downs` WHERE `id`=? LIMIT 1;", [$id]);

if (!empty($down)) {
    if (!empty($down['active'])) {
        while (ob_get_level()) {
            ob_end_clean();
        }
        header("Content-Encoding: none");
        header("Content-type:application/rss+xml; charset=utf-8");
        echo '<?xml version="1.0" encoding="utf-8"?>';
        echo '<rss version="2.0"><channel>';
        echo '<title>Комментарии - '.$down['title'].'</title>';
        echo '<link>'.$config['home'].'</link>';
        echo '<description>Комментарии RSS - '.$config['title'].'</description>';
        echo '<image><url>'.$config['logotip'].'</url>';
        echo '<title>Комментарии - '.$down['title'].'</title>';
        echo '<link>'.$config['home'].'</link></image>';
        echo '<language>ru</language>';
        echo '<copyright>'.$config['copy'].'</copyright>';
        echo '<managingEditor>'.$config['emails'].'</managingEditor>';
        echo '<webMaster>'.$config['emails'].'</webMaster>';
        echo '<lastBuildDate>'.date("r", SITETIME).'</lastBuildDate>';

        $querycomm = DB::run() -> query("SELECT * FROM `comments` WHERE relate_type=? AND `relate_id`=? ORDER BY `time` DESC LIMIT 15;", ['down', $id]);

        while ($data = $querycomm -> fetch()) {
            $data['text'] = App::bbCode($data['text']);
            $data['text'] = str_replace('/images/smiles', $config['home'].'/images/smiles', $data['text']);
            $data['text'] = htmlspecialchars($data['text']);

            echo '<item><title>'.$down['title'].'</title><link>'.$config['home'].'/load/down?act=comments&amp;id='.$down['id'].'</link>';
            echo '<description>'.$data['text'].' </description><author>'.nickname($data['user']).'</author>';
            echo '<pubDate>'.date("r", $data['time']).'</pubDate><category>Комментарии</category><guid>'.$config['home'].'/load/down?act=comments&amp;id='.$down['id'].'&amp;pid='.$data['id'].'</guid></item>';
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
