<!DOCTYPE html>
<html>
<head>
    <title><?=$blog['title']?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>
<body>
    <h1><?=$blog['title']?></h1>

    <?=App::bbCode($blog['text'])?><br /><br />

    URL: <a href="<?= App::setting('home') ?>/blog/blog?act=view&amp;id=<?=$blog['id']?>"><?= App::setting('home')?>/blog/blog?act=view&amp;id=<?=$blog['id']?></a>
</body>
</html>
