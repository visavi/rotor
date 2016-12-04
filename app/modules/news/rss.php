<?= '<?xml version="1.0" encoding="utf-8"?>' ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>Новости - <?=App::setting('title')?></title>
        <link><?=App::setting('home')?></link>
        <description>RSS новостей - <?=App::setting('title')?></description>
        <image>
            <url><?=App::setting('logotip')?></url>
            <title>Новости - <?=App::setting('title')?></title>
            <link><?=App::setting('home')?></link>
        </image>
        <language>ru</language>
        <copyright><?=App::setting('copy')?></copyright>
        <managingEditor><?=App::setting('emails')?> (<?=App::setting('nickname')?>)</managingEditor>
        <webMaster><?=App::setting('emails')?> (<?=App::setting('nickname')?>)</webMaster>
        <lastBuildDate><?=date("r", SITETIME)?></lastBuildDate>

        <?php $querynews = DB::run() -> query("SELECT * FROM `news` ORDER BY `id` DESC LIMIT 15;"); ?>
        <?php while ($news = $querynews -> fetch()): ?>
            <?php $news['text'] = App::bbCode($news['text']); ?>
            <?php $news['text'] = str_replace(['/upload/smiles', '[cut]'], [App::setting('home').'/upload/smiles', ''], $news['text']); ?>
            <?php $news['text'] = htmlspecialchars($news['text']); ?>

            <item>
                <title><?=$news['title']?></title>
                <link><?=App::setting('home')?>/news/<?=$news['id']?></link>
                <description><?=$news['text']?> </description><author><?=nickname($news['author'])?></author>
                <pubDate><?=date("r", $news['time'])?></pubDate>
                <category>Новости</category>
                <guid><?=App::setting('home')?>/news/<?=$news['id']?></guid>
            </item>
        <?php endwhile; ?>

    </channel>
</rss>

