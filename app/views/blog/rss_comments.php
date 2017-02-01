<?= '<?xml version="1.0" encoding="utf-8"?>' ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title><?= $blog['title'] ?></title>
        <link><?= App::setting('home') ?>/</link>
        <description><?= App::setting('title') ?></description>
        <image>
            <url><?= App::setting('home') ?><?= App::setting('logotip') ?></url>
            <title><?= $blog['title'] ?></title>
            <link><?= App::setting('home') ?>/</link>
        </image>
        <managingEditor><?=App::setting('emails')?> (<?=App::setting('nickname')?>)</managingEditor>
        <webMaster><?=App::setting('emails')?> (<?=App::setting('nickname')?>)</webMaster>
        <lastBuildDate><?=date("r", SITETIME)?></lastBuildDate>

        <?php foreach ($blog->lastComments as $data): ?>
            <?php $data['text'] = App::bbCode($data['text']); ?>
            <?php $data['text'] = str_replace('/uploads/smiles', App::setting('home').'/uploads/smiles', $data['text']); ?>
            <?php $data['text'] = htmlspecialchars($data['text']); ?>

            <item>
                <title><?=$blog['text']?></title>
                <link><?=App::setting('home')?>/blog/blog?act=comments&amp;id=<?=$blog['id']?></link>
                <description><?=$data['title']?></description><author><?=nickname($data['user'])?></author>
                <pubDate><?=date("r", $data['time'])?></pubDate>
                <category>Комментарии</category>
                <guid><?=App::setting('home')?>/blog/blog?act=comments&amp;id=<?=$blog['id']?>&amp;pid=<?=$data['id']?></guid>
            </item>
        <?php endforeach; ?>

    </channel>
</rss>
