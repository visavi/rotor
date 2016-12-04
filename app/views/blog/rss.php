<?= '<?xml version="1.0" encoding="utf-8"?>' ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>Блоги - <?=App::setting('title')?></title>
        <link><?=App::setting('home')?></link>
        <description>RSS блогов - <?=App::setting('title')?></description>
        <image>
            <url><?=App::setting('logotip')?></url>
            <title>Блоги - <?=App::setting('title')?></title>
            <link><?=App::setting('home')?></link>
        </image>
        <language>ru</language>
        <copyright><?=App::setting('copy')?></copyright>
        <managingEditor><?=App::setting('emails')?> (<?=App::setting('nickname')?>)</managingEditor>
        <webMaster><?=App::setting('emails')?> (<?=App::setting('nickname')?>)</webMaster>
        <lastBuildDate><?=date("r", SITETIME)?></lastBuildDate>

        <?php foreach ($blogs as $blog): ?>
            <?php $blog['text'] = App::bbCode($blog['text']); ?>
            <?php $blog['text'] = str_replace('/upload/smiles', App::setting('home').'/upload/smiles', $blog['text']); ?>
            <?php $blog['title'] = htmlspecialchars($blog['title']); ?>
            <?php $blog['text'] = htmlspecialchars($blog['text']); ?>

            <item>
                <title><?=$blog['title']?></title>
                <link><?=App::setting('home')?>/blog/blog?act=view&amp;id=<?=$blog['id']?></link>
                <description><?=$blog['text']?> </description><author><?=nickname($blog['user'])?></author>
                <pubDate><?=date("r", $blog['time'])?></pubDate>
                <category>Блоги</category>
                <guid><?=App::setting('home')?>/blog/blog?act=view&amp;id=<?=$blog['id']?></guid>
            </item>
        <?php endforeach; ?>

    </channel>
</rss>
