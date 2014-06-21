<img src="/images/img/edit.gif" alt="image" /> <b><?=profile($post['guest_user'])?></b> <small>(<?=date_fixed($post['guest_time'])?>)</small><br /><br />

<div class="form">
	<form action="index.php?act=editpost&amp;id=<?=$id?>&amp;start=<?=$start?>&amp;uid=<?=$_SESSION['token']?>" method="post">
		<textarea id="markItUp" cols="25" rows="5" name="msg"><?=$post['guest_text']?></textarea><br />
		<input value="Редактировать" type="submit" />
	</form>
</div><br />
