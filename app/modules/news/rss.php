<?= '<?xml version="1.0" encoding="utf-8"?>' ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>Новости - <?=Setting::get('title')?></title>
        <link><?=Setting::get('home')?></link>
        <description>RSS новостей - <?=Setting::get('title')?></description>
        <image>
            <url><?=Setting::get('logotip')?></url>
            <title>Новости - <?=Setting::get('title')?></title>
            <link><?=Setting::get('home')?></link>
        </image>
        <language>ru</language>
        <copyright><?=Setting::get('copy')?></copyright>
        <managingEditor><?=Setting::get('emails')?> (<?=Setting::get('nickname')?>)</managingEditor>
        <webMaster><?=Setting::get('emails')?> (<?=Setting::get('nickname')?>)</webMaster>
        <lastBuildDate><?=date("r", SITETIME)?></lastBuildDate>

        <?php $newses = News::orderBy('created_at', 'desc')->limit(15)->get(); ?>
        <?php foreach($newses as $news): ?>
            <?php $news['text'] = App::bbCode($news['text']); ?>
            <?php $news['text'] = str_replace(['/uploads/smiles', '[cut]'], [Setting::get('home').'/uploads/smiles', ''], $news['text']); ?>
            <?php $news['text'] = htmlspecialchars($news['text']); ?>

            <item>
                <title><?=$news['title']?></title>
                <link><?=Setting::get('home')?>/news/<?=$news['id']?></link>
                <description><?=$news['text']?> </description>
                <author><?=$news->getUser()->login?></author>
                <pubDate><?=date("r", $news['created_at'])?></pubDate>
                <category>Новости</category>
                <guid><?=Setting::get('home')?>/news/<?=$news['id']?></guid>
            </item>
        <?php endforeach; ?>

    </channel>
</rss>

