<i class="fa fa-pencil"></i> <b><?=nickname($post['user'])?></b> <small>(<?=date_fixed($post['time'])?>)</small><br /><br />

<div class="form">
	<form action="/blog/blog?act=editpost&amp;id=<?=$post['relate_id']?>&amp;pid=<?=$pid?>&amp;page=<?=$page?>&amp;uid=<?=$_SESSION['token']?>" method="post">
		<textarea id="markItUp" cols="25" rows="5" name="msg"><?=$post['text']?></textarea><br />
		<input type="submit" value="Редактировать" />
	</form>
</div><br />
