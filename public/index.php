<?php
/**
 * Rotor - Content management system
 *
 * @package   Rotor
 * @author    Vantuz <admin@visavi.net>
 * @link      http://visavi.net
 * @copyright 2005-2018
 */

require __DIR__ . '/../app/bootstrap.php';

ob_start();
session_start();
date_default_timezone_set(setting('timezone'));

$app = new \App\Classes\Application();
$app->run();
