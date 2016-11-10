<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
<head>
	<title><?=$blog['title']?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>
<body>
	<h2><?=$blog['title']?></h2>

	<?=App::bbCode($blog['text'])?><br /><br />

	URL: <a href="<?=$config['home']?>/blog/blog?act=view&amp;id=<?=$blog['id']?>"><?=$config['home']?>/blog/blog?act=view&amp;id=<?=$blog['id']?></a>
</body>
</html>
