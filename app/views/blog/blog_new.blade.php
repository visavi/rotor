<a href="/blog">Блоги</a> /
<a href="/blog/search">Поиск</a> /
<a href="/blog/blog?act=blogs">Все статьи</a><hr>


<div class="form next">
	<form action="/blog/blog?act=addblog&amp;uid=<?=$_SESSION['token']?>" method="post">

		Категория*:<br>
		<select name="cid">
		<option value="0">Выберите категорию</option>

		<?php foreach ($cats as $key => $data): ?>
			<?php $selected = ($cid == $data['id']) ? ' selected="selected"' : ''; ?>
			<option value="<?=$data['id']?>"<?=$selected?>><?=$data['name']?></option>
		<?php endforeach; ?>

		</select><br>

		Заголовок:<br>
		<input type="text" name="title" size="50" maxlength="50"><br>
		Текст:<br>
		<textarea id="markItUp" cols="25" rows="10" name="text"></textarea><br>
		Метки:<br>
		<input type="text" name="tags" size="50" maxlength="100"><br>

		<input value="Опубликовать" type="submit">
	</form>
</div><br>

Рекомендация! Для разбивки статьи по страницам используйте тег [nextpage]<br>
Метки статьи должны быть от 2 до 20 символов с общей длиной не более 50 символов<br><br>

<a href="/rules">Правила</a> /
<a href="/smiles">Смайлы</a> /
<a href="/tags">Теги</a><br><br>
