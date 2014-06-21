<b><big>Ответ на сообщение</big></b><br /><br />

<div class="b">
	<img src="/images/img/edit.gif" alt="image" /> <b><?=profile($post['commblog_author'])?></b> <?=user_title($post['commblog_author'])?> <?=user_online($post['commblog_author'])?> <small>(<?=date_fixed($post['commblog_time'])?>)</small>
</div>
<div>
	Сообщение: <?=bb_code($post['commblog_text'])?>
</div><hr />

<div class="form">
	<form action="blog.php?act=add&amp;id=<?=$id?>&amp;uid=<?=$_SESSION['token']?>" method="post">
		<textarea id="markItUp" cols="25" rows="5" name="msg">[b]<?=nickname($post['commblog_author'])?>[/b], </textarea><br />
		<input type="submit" value="Ответить" />
	</form>
</div><br />
