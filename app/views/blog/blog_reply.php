<b><big>Ответ на сообщение</big></b><br /><br />

<div class="b">
	<i class="fa fa-pencil"></i> <b><?=profile($post['user'])?></b> <?=user_title($post['user'])?> <?=user_online($post['user'])?> <small>(<?=date_fixed($post['time'])?>)</small>
</div>
<div>
	Сообщение: <?=App::bbCode($post['text'])?>
</div><hr />

<div class="form">
	<form action="/blog/blog?act=add&amp;id=<?=$id?>&amp;uid=<?=$_SESSION['token']?>" method="post">
		<textarea id="markItUp" cols="25" rows="5" name="msg">[b]<?=nickname($post['user'])?>[/b], </textarea><br />
		<input type="submit" value="Ответить" />
	</form>
</div><br />
