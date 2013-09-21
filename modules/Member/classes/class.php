<?php
defined('ACTIVE_MODULE') or die('Restricted access');

require_once("member.world.class.php");

# Snowflake DB stuff
#require_once('conf.member.db.php');
require_once('snow.class.php');
#require_once('class.member.db.php');

# Snowflake functionen
require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/eveTime.php');
require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/listNews.php');
require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/listMembers.php');
require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/listDivs.php');
require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/listFlags.php');
require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/listStats.php');
require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/listEval.php');

require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/class.kill.php');
require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/class.ratter.php');

require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/showChar.php');
require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/addFlags.php');
require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/isAlt.php');
require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/editEval.php');
require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/addEval.php');
?>