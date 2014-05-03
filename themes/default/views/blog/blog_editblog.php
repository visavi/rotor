<a href="/blog">Блоги</a> /
<a href="/blog/search.php">Поиск</a> /
<a href="/blog/blog.php?act=blogs">Все статьи</a><hr />

<b><big>Редактирование</big></b><br /><br />

<div class="form">
	<form action="blog.php?act=changeblog&amp;id=<?=$blogs['blogs_id']?>&amp;uid=<?=$_SESSION['token']?>" method="post">

		Раздел:<br />
		<select name="cats">

		<?php foreach ($cats as $data): ?>
			<?php $selected = ($blogs['blogs_cats_id'] == $data['cats_id']) ? ' selected="selected"' : ''; ?>
			<option value="<?=$data['cats_id']?>"<?=$selected?>><?=$data['cats_name']?></option>
		<?php endforeach; ?>

		</select><br />

		Заголовок:<br />
		<input type="text" name="title" size="50" maxlength="50" value="<?=$blogs['blogs_title']?>" /><br />
		Текст:<br />
		<textarea id="markItUp" cols="25" rows="15" name="text"><?=$blogs['blogs_text']?></textarea><br />
		Метки:<br />
		<input type="text" name="tags" size="50" maxlength="100" value="<?=$blogs['blogs_tags']?>" /><br />

		<input type="submit" value="Изменить" />
	</form>
</div><br />

<a href="/pages/rules.php">Правила</a> /
<a href="/pages/smiles.php">Смайлы</a> /
<a href="/pages/tags.php">Теги</a><br /><br />
