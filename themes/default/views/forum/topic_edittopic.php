<h2>Изменение темы</h2>

<img src="/images/img/edit.gif" alt="image" /> <b><?=nickname($post['posts_user'])?></b> <small>(<?=date_fixed($post['posts_time'])?>)</small><br /><br />

<div class="form">
	<form action="topic.php?act=changetopic&amp;tid=<?=$post['posts_topics_id']?>&amp;uid=<?=$_SESSION['token']?>" method="post">

		Заголовок:<br />
		<input type="text" name="title" size="50" maxlength="50" value="<?=$topics['topics_title']?>" /><br />
		<textarea id="markItUp" cols="25" rows="5" name="msg"><?=$post['posts_text']?></textarea><br />
		<input type="submit" value="Редактировать" />
	</form>
</div>
<br />

<a href="/pages/smiles.php">Смайлы</a>  /
<a href="/pages/tags.php">Теги</a>  /
<a href="/pages/rules.php">Правила</a><br />
