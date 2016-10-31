<?php

if (isAjaxRequest()){
	$message = isset($_POST['data']) ? check($_POST['data']) : '';
?>
	<!doctype html>

	<html lang="ru">
	<head>
		<meta charset="utf-8">
		<title>RotorCMS</title>
		<?= include_style() ?>
		<?= include_javascript() ?>
	</head>
	<body>
		<?php exit(bb_code($message)); ?>
	</body>
	</html>
<?php } ?>
