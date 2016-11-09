<b><big>Цитирование</big></b><br /><br />

<div class="form">
	<form action="/blog/blog?act=add&amp;id=<?=$id?>&amp;uid=<?=$_SESSION['token']?>" method="post">
		<textarea id="markItUp" cols="25" rows="5" name="msg">[quote][b]<?=nickname($post['user'])?>[/b] (<?=date_fixed($post['time'])?>)<?=PHP_EOL.$post['text']?>[/quote]<?=PHP_EOL?></textarea><br />
		<input type="submit" value="Цитировать" />
	</form>
</div><br />
