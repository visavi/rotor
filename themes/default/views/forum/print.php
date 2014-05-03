<?php header('Content-type:text/html; charset=utf-8'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
<head>
	<title><?=$topic['topics_title']?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>
<body>

<h2><?=$topic['topics_title']?></h2>

<?php foreach ($posts as $key => $data): ?>

	<?=($key + 1)?>. <b><?=nickname($data['posts_user'])?></b> (<?=date_fixed($data['posts_time'])?>)<br />
	<?=bb_code($data['posts_text'])?>
	<br /><br />

<?php endforeach; ?>

URL: <a href="<?=$config['home']?>/forum/topic.php?tid=<?=$topic['topics_id']?>"><?=$config['home']?>/forum/topic.php?tid=<?=$topic['topics_id']?></a>
</body>
</html>
