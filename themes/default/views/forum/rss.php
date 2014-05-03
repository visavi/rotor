<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
	<channel>
		<title>Сообщения - <?=$topic['topics_title']?></title>
		<link><?=$config['home']?></link>
		<description>Сообщения RSS - <?=$config['title']?></description>
		<image>
			<url><?=$config['logotip']?></url>
			<title>Сообщения - <?=$topic['topics_title']?></title>
			<link><?=$config['home']?></link>
		</image>
		<language>ru</language>
		<copyright><?=$config['copy']?></copyright>
		<managingEditor><?=$config['emails']?> (<?=$config['nickname']?>)</managingEditor>
		<webMaster><?=$config['emails']?> (<?=$config['nickname']?>)</webMaster>
		<lastBuildDate><?=date("r", SITETIME)?></lastBuildDate>

		<?php foreach ($posts as $data): ?>
			<?php $data['posts_text'] = bb_code($data['posts_text']); ?>
			<?php $data['posts_text'] = str_replace('/images/smiles', $config['home'].'/images/smiles', $data['posts_text']); ?>
			<?php $data['posts_text'] = htmlspecialchars($data['posts_text']); ?>

			<item>
				<title><?=$topic['topics_title']?></title>
				<link><?=$config['home']?>/forum/topic.php?tid=<?=$topic['topics_id']?></link>
				<description><?=$data['posts_text']?> </description><author><?=nickname($data['posts_user'])?></author>
				<pubDate><?=date("r", $data['posts_time'])?></pubDate>
				<category>Сообщения</category>
				<guid><?=$config['home']?>/forum/topic.php?tid=<?=$topic['topics_id']?>&amp;pid=<?=$data['posts_id']?></guid>
			</item>
		<?php endforeach; ?>

	</channel>
</rss>

