<?php

include_once (APP.'/views/advert/forum.blade.php');

/*$forums = Forum::where('parent_id', 0)
    ->with('countTopic', 'countPost', 'children.countTopic', 'lastTopic.lastPost.user')
    ->order_by_asc('sort')
    ->find_many();*/

$forums = NewForum::where('parent_id', 0)
    ->with('countTopic', 'countPost', 'children.countTopic', 'children.countPost', 'lastTopic')
    ->orderBy('sort')
    ->get();

function logger() {
    $queries = Capsule::getQueryLog();
    $formattedQueries = [];
    foreach( $queries as $query ) :
        $prep = $query['query'];
        foreach( $query['bindings'] as $binding ) :
            $prep = preg_replace("#\?#", $binding, $prep, 1);
        endforeach;
        $formattedQueries[] = $prep;
    endforeach;
    return $formattedQueries;
}


var_dump(logger(), $forums); exit;

if (empty(count($forums))) {
    App::abort('default', 'Разделы форума еще не созданы!');
}

App::view('forum/index', compact('forums'));
