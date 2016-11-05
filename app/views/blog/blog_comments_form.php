<div class="form">
	<form action="/blog/blog?act=add&amp;id=<?=$blogs['id']?>&amp;uid=<?=$_SESSION['token']?>" method="post">
		<textarea id="markItUp" cols="25" rows="5" name="msg"></textarea><br />
		<input type="submit" value="Написать" />
	</form>
</div><br />

<a href="/rules">Правила</a> /
<a href="/smiles">Смайлы</a> /
<a href="/tags">Теги</a><br /><br />
