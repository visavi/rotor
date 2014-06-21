<b><big>Цитирование</big></b><br /><br />

<div class="form">
	<form action="blog.php?act=add&amp;id=<?=$id?>&amp;uid=<?=$_SESSION['token']?>" method="post">
		<textarea id="markItUp" cols="25" rows="5" name="msg">[q][b]<?=nickname($post['commblog_author'])?>[/b] (<?=date_fixed($post['commblog_time'])?>)<?=PHP_EOL.$post['commblog_text']?>[/q]<?=PHP_EOL?></textarea><br />
		<input type="submit" value="Цитировать" />
	</form>
</div><br />
