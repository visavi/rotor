<a href="/blog">Блоги</a> /
<a href="/blog/search">Поиск</a> /
<a href="/blog/blog?act=blogs">Все статьи</a><hr />

<b><big>Редактирование</big></b><br /><br />

<div class="form">
	<form action="/blog/blog?act=changeblog&amp;id=<?=$blogs['id']?>&amp;uid=<?=$_SESSION['token']?>" method="post">

		Раздел:<br />
		<select name="cats">

		<?php foreach ($cats as $data): ?>
			<?php $selected = ($blogs['category_id'] == $data['id']) ? ' selected="selected"' : ''; ?>
			<option value="<?=$data['id']?>"<?=$selected?>><?=$data['name']?></option>
		<?php endforeach; ?>

		</select><br />

		Заголовок:<br />
		<input type="text" name="title" size="50" maxlength="50" value="<?=$blogs['title']?>" /><br />
		Текст:<br />
		<textarea id="markItUp" cols="25" rows="15" name="text"><?=$blogs['text']?></textarea><br />
		Метки:<br />
		<input type="text" name="tags" size="50" maxlength="100" value="<?=$blogs['tags']?>" /><br />

		<input type="submit" value="Изменить" />
	</form>
</div><br />

<a href="/rules">Правила</a> /
<a href="/smiles">Смайлы</a> /
<a href="/tags">Теги</a><br /><br />
