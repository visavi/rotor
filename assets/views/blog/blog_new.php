<a href="/blog">Блоги</a> /
<a href="/blog/search.php">Поиск</a> /
<a href="/blog/blog.php?act=blogs">Все статьи</a><hr />


<div class="form next">
	<form action="blog.php?act=addblog&amp;uid=<?=$_SESSION['token']?>" method="post">

		Категория*:<br />
		<select name="cid">
		<option value="0">Выберите категорию</option>

		<?php foreach ($cats as $key => $data): ?>
			<?php $selected = ($cid == $data['cats_id']) ? ' selected="selected"' : ''; ?>
			<option value="<?=$data['cats_id']?>"<?=$selected?>><?=$data['cats_name']?></option>
		<?php endforeach; ?>

		</select><br />

		Заголовок:<br />
		<input type="text" name="title" size="50" maxlength="50" /><br />
		Текст:<br />
		<textarea id="markItUp" cols="25" rows="10" name="text"></textarea><br />
		Метки:<br />
		<input type="text" name="tags" size="50" maxlength="100" /><br />

		<input value="Опубликовать" type="submit" />
	</form>
</div><br />

Рекомендация! Для разбивки статьи по страницам используйте тег [nextpage]<br />
Метки статьи должны быть от 2 до 20 символов с общей длиной не более 50 символов<br /><br />

<a href="/pages/rules.php">Правила</a> /
<a href="/pages/smiles.php">Смайлы</a> /
<a href="/pages/tags.php">Теги</a><br /><br />
