<?php if ($config['navigation'] == 1): ?>
	<form method="post" action="/pages/index.php">
		<select name="link">
			<option value="index.php">Быстрый переход</option>

		<?php foreach($navigation as $val): ?>
			<option value="<?=$val['nav_url']?>"><?=$val['nav_title']?></option>
		<?php endforeach; ?>

		</select>
	<input value="Go!" type="submit" /></form>
<?php endif; ?>

<?php if ($config['navigation'] == 2): ?>
	<form method="post" action="/pages/index.php">
		<select name="link" onchange="this.form.submit();">
			<option value="index.php">Быстрый переход</option>

		<?php foreach($navigation as $val): ?>
			<option value="<?=$val['nav_url']?>"><?=$val['nav_title']?></option>
		<?php endforeach; ?>

		</select>
	</form>
<?php endif; ?>
