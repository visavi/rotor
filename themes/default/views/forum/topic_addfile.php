<div class="form">
	<form action="topic.php?act=add&amp;tid=<?=$tid?>&amp;start=<?=$start?>&amp;uid=<?=$_SESSION['token']?>" method="post" enctype="multipart/form-data">
		<textarea id="markItUp" cols="25" rows="5" name="msg"></textarea><br />
		Прикрепить файл:<br /><input type="file" name="file" /><br />
		<input type="submit" value="Написать" />
	</form>
</div><br />

<div class="info">
	Максимальный вес файла: <b><?=round($config['forumloadsize']/1024)?></b> Kb<br />
	Допустимые расширения: <?=str_replace(',', ', ', $config['forumextload'])?>
</div><br />
