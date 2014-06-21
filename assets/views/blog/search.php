<div class="form">
	<form action="search.php?act=search" method="get">
		<input type="hidden" name="act" value="search" />

		Запрос:<br />
		<input type="text" name="find" size="50" /><br />

		Искать:<br />
		<input name="where" type="radio" value="0" checked="checked" /> В заголовке<br />
		<input name="where" type="radio" value="1" /> В тексте<br /><br />

		Тип запроса:<br />
		<input name="type" type="radio" value="0" checked="checked" /> И<br />
		<input name="type" type="radio" value="1" /> Или<br />
		<input name="type" type="radio" value="2" /> Полный<br /><br />

		<input type="submit" value="Поиск" />
	</form>
</div><br />
