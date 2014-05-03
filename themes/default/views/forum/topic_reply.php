<div class="b">
	<img src="/images/img/edit.gif" alt="image" /> <b><?=profile($params['post']['posts_user'])?></b> <?=user_title($params['post']['posts_user'])?> <?=user_online($params['post']['posts_user'])?> <small>(<?=date_fixed($params['post']['posts_time'])?>)</small>
</div>

<div>
	Сообщение: <?=bb_code($params['post']['posts_text'])?>
</div>
<hr />

<div class="form">
	<form action="topic.php?act=add&amp;tid=<?=$params['post']['posts_topics_id']?>&amp;start=<?=$params['start']?>&amp;uid=<?=$_SESSION['token']?>" method="post" enctype="multipart/form-data">
		<textarea id="markItUp" cols="25" rows="5" name="msg"><?=$params['num']?>. [b]<?=nickname($params['post']['posts_user'])?>[/b], </textarea><br />

		<?php if ($udata['users_point'] >= $config['forumloadpoints']): ?>
			Прикрепить файл:<br /><input type="file" name="file" /><br />
		<?php endif; ?>

		<input type="submit" value="Ответить" />
	</form>
</div><br />

<a href="/pages/smiles.php">Смайлы</a>  /
<a href="/pages/tags.php">Теги</a>  /
<a href="/pages/rules.php">Правила</a><br />
