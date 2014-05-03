<img src="/images/img/edit.gif" alt="image" /> <b><?=nickname($post['commblog_author'])?></b> <small>(<?=date_fixed($post['commblog_time'])?>)</small><br /><br />

<div class="form">
	<form action="/blog/blog.php?act=editpost&amp;id=<?=$post['commblog_blog']?>&amp;pid=<?=$pid?>&amp;start=<?=$start?>&amp;uid=<?=$_SESSION['token']?>" method="post">
		<textarea id="markItUp" cols="25" rows="5" name="msg"><?=$post['commblog_text']?></textarea><br />
		<input type="submit" value="Редактировать" />
	</form>
</div><br />
