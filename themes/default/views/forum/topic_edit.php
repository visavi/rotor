<img src="/images/img/edit.gif" alt="image" /> <b><?=nickname($post['posts_user'])?></b> <small>(<?=date_fixed($post['posts_time'])?>)</small><br /><br />

<div class="form">
	<form action="topic.php?act=editpost&amp;tid=<?=$post['posts_topics_id']?>&amp;pid=<?=$pid?>&amp;start=<?=$start?>&amp;uid=<?=$_SESSION['token']?>" method="post">
		<textarea id="markItUp" cols="25" rows="5" name="msg"><?=$post['posts_text']?></textarea><br />

		<?php if (!empty($files)): ?>
			<img src="/images/img/paper-clip.gif" alt="attach" /> <b>Удаление файлов:</b><br />
			<?php foreach ($files as $file): ?>
				<input type="checkbox" name="delfile[]" value="<?=$file['file_id']?>" />
				<a href="/upload/forum/<?=$file['file_topics_id']?>/<?=$file['file_hash']?>" target="_blank"><?=$file['file_name']?></a> (<?=formatsize($file['file_size'])?>)<br />
			<?php endforeach; ?>
			<br />
		<?php endif; ?>

		<input type="submit" value="Редактировать" />
	</form>
</div>
<br />

<a href="#up"><img src="/images/img/ups.gif" alt="up" /></a>
<a href="/pages/smiles.php">Смайлы</a>  /
<a href="/pages/tags.php">Теги</a>  /
<a href="/pages/rules.php">Правила</a><br />
