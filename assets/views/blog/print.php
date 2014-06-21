<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
<head>
	<title><?=$blog['blogs_title']?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>
<body>
	<h2><?=$blog['blogs_title']?></h2>

	<?=bb_code($blog['blogs_text'])?><br /><br />

	URL: <a href="<?=$config['home']?>/blog/blog.php?act=view&amp;id=<?=$blog['blogs_id']?>"><?=$config['home']?>/blog/blog.php?act=view&amp;id=<?=$blog['blogs_id']?></a>
</body>
</html>
