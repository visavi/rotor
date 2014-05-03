<div class="form">
	<form action="search.php?act=search" method="get">
		<input type="hidden" name="act" value="search" />

		Запрос:<br />
		<input type="text" name="find" /><br />

		Раздел:<br />
		<select name="section">
		<option value="0">Не имеет значения</option>

		<?php foreach ($forums[0] as $key => $data): ?>
			<?php $selected = ($fid == $data['forums_id']) ? ' selected="selected"' : ''; ?>

			<option value="<?=$data['forums_id']?>"<?=$selected?>><?=$data['forums_title']?></option>

			<?php if (isset($forums[$key])): ?>
				<?php foreach($forums[$key] as $datasub): ?>
					<?php $selected = ($fid == $datasub['forums_id']) ? ' selected="selected"' : ''; ?>

					<option value="<?=$datasub['forums_id']?>"<?=$selected?>>– <?=$datasub['forums_title']?></option>

				<?php endforeach; ?>
			<?php endif; ?>
		<?php endforeach; ?>

		</select><br />

		Период:<br />
		<select name="period">
			<option value="0">За все время</option>
			<option value="7">Последние 7 дней</option>
			<option value="30">Последние 30 дней</option>
			<option value="60">Последние 60 дней</option>
			<option value="90">Последние 90 дней</option>
			<option value="180">Последние 180 дней</option>
			<option value="365">Последние 365 дней</option>
		</select>
		<br /><br />

		Искать:<br />
		<input name="where" type="radio" value="0" checked="checked" /> В темах<br />
		<input name="where" type="radio" value="1" /> В сообщениях<br /><br />

		Тип запроса:<br />
		<input name="type" type="radio" value="0" checked="checked" /> И<br />
		<input name="type" type="radio" value="1" /> Или<br />
		<input name="type" type="radio" value="2" /> Полный<br /><br />

		<input type="submit" value="Поиск" />
	</form>
</div>
<br />
