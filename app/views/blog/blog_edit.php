<i class="fa fa-pencil"></i> <b><?=nickname($post['author'])?></b> <small>(<?=date_fixed($post['time'])?>)</small><br /><br />

<div class="form">
	<form action="/blog/blog?act=editpost&amp;id=<?=$post['blog']?>&amp;pid=<?=$pid?>&amp;start=<?=$start?>&amp;uid=<?=$_SESSION['token']?>" method="post">
		<textarea id="markItUp" cols="25" rows="5" name="msg"><?=$post['text']?></textarea><br />
		<input type="submit" value="Редактировать" />
	</form>
</div><br />
