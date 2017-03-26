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

        <?php $newses = News::orderBy('created_at', 'desc')->limit(15)->get(); ?>
        <?php foreach($newses as $news): ?>
            <?php $news['text'] = App::bbCode($news['text']); ?>
            <?php $news['text'] = str_replace(['/uploads/smiles', '[cut]'], [App::setting('home').'/uploads/smiles', ''], $news['text']); ?>
            <?php $news['text'] = htmlspecialchars($news['text']); ?>

            <item>
                <title><?=$news['title']?></title>
                <link><?=App::setting('home')?>/news/<?=$news['id']?></link>
                <description><?=$news['text']?> </description>
                <author><?=$news->getUser()->login?></author>
                <pubDate><?=date("r", $news['created_at'])?></pubDate>
                <category>Новости</category>
                <guid><?=App::setting('home')?>/news/<?=$news['id']?></guid>
            </item>
        <?php endforeach; ?>

    </channel>
</rss>

