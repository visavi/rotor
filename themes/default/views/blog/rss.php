<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
	<channel>
		<title>Комментарии - <?=$blog['blogs_title']?></title>
		<link><?=$config['home']?></link>
		<description>Сообщения RSS - <?=$config['title']?></description>
		<image>
			<url><?=$config['logotip']?></url>
			<title>Комментарии - <?=$blog['blogs_title']?></title>
			<link><?=$config['home']?></link>
		</image>
		<language>ru</language>
		<copyright><?=$config['copy']?></copyright>
		<managingEditor><?=$config['emails']?> (<?=$config['nickname']?>)</managingEditor>
		<webMaster><?=$config['emails']?> (<?=$config['nickname']?>)</webMaster>
		<lastBuildDate><?=date("r", SITETIME)?></lastBuildDate>

		<?php foreach ($comments as $data): ?>
			<?php $data['commblog_text'] = bb_code($data['commblog_text']); ?>
			<?php $data['commblog_text'] = str_replace('/images/smiles', $config['home'].'/images/smiles', $data['commblog_text']); ?>
			<?php $data['commblog_text'] = htmlspecialchars($data['commblog_text']); ?>

			<item>
				<title><?=$blog['blogs_title']?></title>
				<link><?=$config['home']?>/blog/blog.php?act=comments&amp;id=<?=$blog['blogs_id']?></link>
				<description><?=$data['commblog_text']?> </description><author><?=nickname($data['commblog_author'])?></author>
				<pubDate><?=date("r", $data['commblog_time'])?></pubDate>
				<category>Комментарии</category>
				<guid><?=$config['home']?>/blog/blog.php?act=comments&amp;id=<?=$blog['blogs_id']?>&amp;pid=<?=$data['commblog_id']?></guid>
			</item>
		<?php endforeach; ?>

	</channel>
</rss>
