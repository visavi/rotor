<b><big>Ответ на сообщение</big></b><br /><br />

<div class="b">
	<i class="fa fa-pencil"></i> <b><?=profile($post['author'])?></b> <?=user_title($post['author'])?> <?=user_online($post['author'])?> <small>(<?=date_fixed($post['time'])?>)</small>
</div>
<div>
	Сообщение: <?=bb_code($post['text'])?>
</div><hr />

<div class="form">
	<form action="/blog/blog?act=add&amp;id=<?=$id?>&amp;uid=<?=$_SESSION['token']?>" method="post">
		<textarea id="markItUp" cols="25" rows="5" name="msg">[b]<?=nickname($post['author'])?>[/b], </textarea><br />
		<input type="submit" value="Ответить" />
	</form>
</div><br />
