<?php
header("Content-type:application/rss+xml; charset=utf-8");
echo '<?xml version="1.0" encoding="utf-8"?>';
echo '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom"><channel>';
echo '<title>'.$config['title'].' News</title>';
echo '<link>'.$config['home'].'</link>';
echo '<description>Новости RSS - '.$config['title'].'</description>';
echo '<image><url>'.$config['logotip'].'</url>';
echo '<title>'.$config['title'].' News</title>';
echo '<link>'.$config['home'].'</link></image>';
echo '<language>ru</language>';
echo '<copyright>'.$config['copy'].'</copyright>';
echo '<managingEditor>'.$config['emails'].' ('.$config['nickname'].')</managingEditor>';
echo '<webMaster>'.$config['emails'].' ('.$config['nickname'].')</webMaster>';
echo '<lastBuildDate>'.date("r", SITETIME).'</lastBuildDate>';

$querynews = DB::run() -> query("SELECT * FROM `news` ORDER BY `id` DESC LIMIT 15;");

while ($data = $querynews -> fetch()) {

    $data['text'] = App::bbCode($data['text']);
    $data['text'] = str_replace(['/upload/smiles', '[cut]'], [$config['home'].'/upload/smiles', ''], $data['text']);

    echo '<item><title>'.htmlspecialchars($data['title']).'</title><link>'.$config['home'].'/news/'.$data['id'].'</link>';
    echo '<description>'.htmlspecialchars($data['text']).' </description><author>'.nickname($data['author']).'</author>';
    echo '<pubDate>'.date("r", $data['time']).'</pubDate><category>Новости</category><guid>'.$config['home'].'/news/'.$data['id'].'</guid></item>';
}

echo '</channel></rss>';

