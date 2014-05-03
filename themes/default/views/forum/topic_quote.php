<b><big>Цитирование</big></b><br /><br />

<div class="form">
	<form action="topic.php?act=add&amp;tid=<?=$params['post']['posts_topics_id']?>&amp;start=<?=$params['start']?>&amp;uid=<?=$_SESSION['token']?>" method="post" enctype="multipart/form-data">
		<textarea id="markItUp" cols="25" rows="5" name="msg">[q][b]<?=nickname($params['post']['posts_user'])?>[/b] (<?=date_fixed($params['post']['posts_time'], 'j F Y / H:i')?>)<?=PHP_EOL?><?=$params['post']['posts_text']?>[/q]<?=PHP_EOL?></textarea><br />

		<?php if ($udata['users_point'] >= $config['forumloadpoints']): ?>
			Прикрепить файл:<br /><input type="file" name="file" /><br />
		<?php endif; ?>

		<input type="submit" value="Цитировать" />
	</form>
</div><br />

<a href="/pages/smiles.php">Смайлы</a>  /
<a href="/pages/tags.php">Теги</a>  /
<a href="/pages/rules.php">Правила</a><br />
