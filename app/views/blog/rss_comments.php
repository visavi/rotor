<?= '<?xml version="1.0" encoding="utf-8"?>' ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>Комментарии - <?=$blog['title']?></title>
        <link><?=App::setting('home')?></link>
        <description>Сообщения RSS - <?=App::setting('title')?></description>
        <image>
            <url><?=App::setting('logotip')?></url>
            <title>Комментарии - <?=$blog['title']?></title>
            <link><?=App::setting('home')?></link>
        </image>
        <language>ru</language>
        <copyright><?=App::setting('copy')?></copyright>
        <managingEditor><?=App::setting('emails')?> (<?=App::setting('nickname')?>)</managingEditor>
        <webMaster><?=App::setting('emails')?> (<?=App::setting('nickname')?>)</webMaster>
        <lastBuildDate><?=date("r", SITETIME)?></lastBuildDate>

        <?php foreach ($comments as $data): ?>
            <?php $data['text'] = App::bbCode($data['text']); ?>
            <?php $data['text'] = str_replace('/upload/smiles', App::setting('home').'/upload/smiles', $data['text']); ?>
            <?php $data['text'] = htmlspecialchars($data['text']); ?>

            <item>
                <title><?=$blog['title']?></title>
                <link><?=App::setting('home')?>/blog/blog?act=comments&amp;id=<?=$blog['id']?></link>
                <description><?=$data['text']?> </description><author><?=nickname($data['user'])?></author>
                <pubDate><?=date("r", $data['time'])?></pubDate>
                <category>Комментарии</category>
                <guid><?=App::setting('home')?>/blog/blog?act=comments&amp;id=<?=$blog['id']?>&amp;pid=<?=$data['id']?></guid>
            </item>
        <?php endforeach; ?>

    </channel>
</rss>
