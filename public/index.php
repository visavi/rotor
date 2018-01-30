<?php
/**
 * Rotor - Content management system
 *
 * @package   Rotor
 * @author    Vantuz <admin@visavi.net>
 * @link      http://visavi.net
 * @copyright 2005-2018
 */

include_once __DIR__.'/../app/start.php';

var_dump(bbCode('[hide]wedwed[/hide]

[hide]vfdvsdfvsdfv[/hide]


[youtube]http://www.youtube.com/watch?v=0cfRQohD226&fmt=18[/youtube]

aaa

[hide]werfrewf[/hide]
wedwed
[youtube]http://www.youtube.com/watch?v=0cfRQohD22Y[/youtube]'));


App\Classes\Application::run();
