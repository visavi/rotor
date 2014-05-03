<img src="/images/img/edit.gif" alt="image" /> <b><?=nickname($params['post']['posts_user'])?></b> <small>(<?=date_fixed($params['post']['posts_time'])?>)</small><br /><br />

<div class="form">
	<form action="topic.php?act=modeditpost&amp;tid=<?=$params['post']['posts_topics_id']?>&amp;pid=<?=$params['pid']?>&amp;start=<?=$params['start']?>&amp;uid=<?=$_SESSION['token']?>" method="post">
		<textarea id="markItUp" cols="25" rows="5" name="msg"><?=$params['post']['posts_text']?></textarea><br />
		<input type="submit" value="Редактировать" />
	</form>
	</div>
<br />

<a href="#up"><img src="/images/img/ups.gif" alt="up" /></a>
<a href="/pages/smiles.php">Смайлы</a>  /
<a href="/pages/tags.php">Теги</a>  /
<a href="/pages/rules.php">Правила</a><br />
