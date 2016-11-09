<?= '<?xml version="1.0" encoding="utf-8"?>' ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
	<channel>
		<title>Комментарии - <?=$blog['title']?></title>
		<link><?=$config['home']?></link>
		<description>Сообщения RSS - <?=$config['title']?></description>
		<image>
			<url><?=$config['logotip']?></url>
			<title>Комментарии - <?=$blog['title']?></title>
			<link><?=$config['home']?></link>
		</image>
		<language>ru</language>
		<copyright><?=$config['copy']?></copyright>
		<managingEditor><?=$config['emails']?> (<?=$config['nickname']?>)</managingEditor>
		<webMaster><?=$config['emails']?> (<?=$config['nickname']?>)</webMaster>
		<lastBuildDate><?=date("r", SITETIME)?></lastBuildDate>

		<?php foreach ($comments as $data): ?>
			<?php $data['text'] = bb_code($data['text']); ?>
			<?php $data['text'] = str_replace('/images/smiles', $config['home'].'/images/smiles', $data['text']); ?>
			<?php $data['text'] = htmlspecialchars($data['text']); ?>

			<item>
				<title><?=$blog['title']?></title>
				<link><?=$config['home']?>/blog/blog?act=comments&amp;id=<?=$blog['id']?></link>
				<description><?=$data['text']?> </description><author><?=nickname($data['user'])?></author>
				<pubDate><?=date("r", $data['time'])?></pubDate>
				<category>Комментарии</category>
				<guid><?=$config['home']?>/blog/blog?act=comments&amp;id=<?=$blog['id']?>&amp;pid=<?=$data['id']?></guid>
			</item>
		<?php endforeach; ?>

	</channel>
</rss>
