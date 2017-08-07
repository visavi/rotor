<?php

$tag = urldecode(param('tag'));

if ($tag) {
    if (! is_utf($tag)){
        $tag = win_to_utf($tag);
    }

    if (utf_strlen($tag) < 2) {
        App::setFlash('danger', 'Ошибка! Необходимо не менее 2-х символов в запросе!');
        App::redirect('/blog/tags');
    }

    if (empty($_SESSION['findresult']) || empty($_SESSION['blogfind']) || $tag!=$_SESSION['blogfind']) {

        $result = Blog::select('id')
            ->where('tags', 'like', '%'.$tag.'%')
            ->limit(500)
            ->pluck('id')
            ->all();

        $_SESSION['blogfind'] = $tag;
        $_SESSION['findresult'] = $result;
    }

    $total = count($_SESSION['findresult']);
    $page = App::paginate(Setting::get('blogpost'), $total);

    $blogs = Blog::select('blogs.*', 'catsblog.name')
        ->whereIn('blogs.id', $_SESSION['findresult'])
        ->join('catsblog', 'blogs.category_id', '=', 'catsblog.id')
        ->orderBy('created_at', 'desc')
        ->offset($page['offset'])
        ->limit(Setting::get('blogpost'))
        ->with('user')
        ->get();

    App::view('blog/tags_search', compact('blogs', 'tag', 'page'));

} else {
    if (@filemtime(STORAGE."/temp/tagcloud.dat") < time() - 3600) {

        $tags =  Blog::select('tags')
            ->pluck('tags')
            ->all();

        $alltag = implode(',', $tags);

        $dumptags = preg_split('/[\s]*[,][\s]*/s', $alltag);
        $tags = array_count_values(array_map('utf_lower', $dumptags));

        arsort($tags);
        array_splice($tags, 100);
        shuffle_assoc($tags);

        file_put_contents(STORAGE."/temp/tagcloud.dat", serialize($tags), LOCK_EX);
    }

    $tags = unserialize(file_get_contents(STORAGE."/temp/tagcloud.dat"));

    $max = max($tags);
    $min = min($tags);

    App::view('blog/tags', compact('tags', 'max', 'min'));
}
