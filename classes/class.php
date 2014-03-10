<?php
defined('FSR_BASE') or die('Restricted access');

$version = '1.2';

require_once("functions.php");
require_once("settings.class.php");
require_once("db.conf.php");
require_once(FSR_BASE . DIRECTORY_SEPARATOR .'Smarty3/Smarty.class.php');
require_once("messages.class.php");
require_once("database.class.php");
require_once("user.class.php");
require_once("world.class.php");
require_once('mail/phpmailer.inc.php');

require_once('ale/factory.php');

//require_once('DBsqlite.php');

?>