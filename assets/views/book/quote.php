<h2>Цитирование</h2>

<div class="form">
	<form action="index.php?act=add&amp;uid=<?=$_SESSION['token']?>" method="post">
		<textarea id="markItUp" cols="25" rows="5" name="msg">[quote][b]<?=nickname($post['guest_user'])?>[/b] (<?=date_fixed($post['guest_time'])?>)<?=PHP_EOL?><?=$post['guest_text']?>[/quote]<?=PHP_EOL?></textarea><br />
		<input type="submit" value="Цитировать" />
	</form>
</div><br />
