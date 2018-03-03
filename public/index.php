<?php
/**
 * Rotor - Content management system
 *
 * @package   Rotor
 * @author    Vantuz <admin@visavi.net>
 * @link      http://visavi.net
 * @copyright 2005-2018
 */
use Illuminate\Database\Capsule\Manager as DB;
include_once __DIR__.'/../app/start.php';


/*DB::update('update forums set last_topic_id = (select id from topics where forums.id = topics.forum_id order by updated_at desc limit 1)');*/


App\Classes\Application::run();
