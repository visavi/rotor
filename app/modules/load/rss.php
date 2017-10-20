<?php
view(setting('themes').'/index');

$id = isset($_GET['id']) ? abs(intval($_GET['id'])) : 0;

//show_title('Комментарии');

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
        echo '<link>'.siteUrl().'</link>';
        echo '<description>Комментарии RSS - '.setting('title').'</description>';
        echo '<image><url>'.setting('logotip').'</url>';
        echo '<title>Комментарии - '.$down['title'].'</title>';
        echo '<link>'.siteUrl().'</link></image>';
        echo '<language>ru</language>';
        echo '<copyright>'.setting('copy').'</copyright>';
        echo '<managingEditor>'.env('SITE_EMAIL').'</managingEditor>';
        echo '<webMaster>'.env('SITE_EMAIL').'</webMaster>';
        echo '<lastBuildDate>'.date("r", SITETIME).'</lastBuildDate>';

        $querycomm = DB::select("SELECT * FROM `comments` WHERE relate_type=? AND `relate_id`=? ORDER BY `time` DESC LIMIT 15;", ['down', $id]);

        while ($data = $querycomm -> fetch()) {
            $data['text'] = bbCode($data['text']);
            $data['text'] = str_replace('/uploads/smiles', siteUrl().'/uploads/smiles', $data['text']);
            $data['text'] = htmlspecialchars($data['text']);

            echo '<item><title>'.$down['title'].'</title><link>'.siteUrl().'/load/down?act=comments&amp;id='.$down['id'].'</link>';
            echo '<description>'.$data['text'].' </description><author>'.$data['user'].'</author>';
            echo '<pubDate>'.date("r", $data['time']).'</pubDate><category>Комментарии</category><guid>'.siteUrl().'/load/down?act=comments&amp;id='.$down['id'].'&amp;pid='.$data['id'].'</guid></item>';
        }

        echo '</channel></rss>';
        exit;
    } else {
        showError('Ошибка! Данный файл еще не проверен модератором!');
    }
} else {
    showError('Ошибка! Выбранный вами файл не существует, возможно он был удален!');
}

echo '<i class="fa fa-arrow-circle-up"></i> <a href="/load">Категории</a>';

view(setting('themes').'/foot');
