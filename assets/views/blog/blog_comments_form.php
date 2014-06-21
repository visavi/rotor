<div class="form">
	<form action="/blog/blog.php?act=add&amp;id=<?=$blogs['blogs_id']?>&amp;uid=<?=$_SESSION['token']?>" method="post">
		<textarea id="markItUp" cols="25" rows="5" name="msg"></textarea><br />
		<input type="submit" value="Написать" />
	</form>
</div><br />

<a href="/pages/rules.php">Правила</a> /
<a href="/pages/smiles.php">Смайлы</a> /
<a href="/pages/tags.php">Теги</a><br /><br />
