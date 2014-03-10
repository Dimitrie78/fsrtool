<?php
defined('FSR_BASE') or die('Restricted access');
#######################################################################
## Lokale Konfigurationsdatei                                        ##
##                                                                   ##
## Version 1.00, Thomas Heuer, 28.03.2010                            ##
##                                                                   ##
#######################################################################


########################################################################

# Database
define('CONFIG', dirname(__FILE__).'/../config/dbconfig.ini');

if(is_file(CONFIG)) {
	$c = Settings::getInstance(CONFIG);
	define('TBL_PREFIX', $c->dbprefix);
	define('TBL_EVEDB', $c->dbeve);
	define('PWSALT', $c->salt);
	define('EMAIL', $c->email);
} else if(strpos($_SERVER['SCRIPT_NAME'], 'install.php') === false) {
	echo 'sorry, not installed!!!';
	die;
}

########################################################################
# General config
if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') $http = 'https://'; else $http = 'http://';
/* get the domain name. */
$DOMAIN = $_SERVER['HTTP_HOST'];
$SCRIPT = dirname($_SERVER['SCRIPT_NAME']);
define('URL', $http . $DOMAIN . $SCRIPT);
if ( $SCRIPT == '/' ) $SCRIPT = '';
define('CONFIG_URL', $http . $DOMAIN . $SCRIPT . '/');
define('URL_INDEX',	 $http . $DOMAIN . $SCRIPT . '/index.php');
define('URL_DOWORK', $http . $DOMAIN . $SCRIPT . '/dowork.php');
define('MODULE_DIR', './modules/');
define('XML_CACHE',  './xmlcache/');
define('IMG_CACHE',  'cache/imgcache/');
define('IMG_URL', 	 'icons/');

########################################################################
# E-Mail-Adress

define('mailFrom',	'webmaster@heuer-humfeld.de');

########################################################################
# Smarty-Config

define('smarty_path',	  'smarty/');
define('smarty_template', 'templates/');
define('smarty_compile',  'cache/templates_c/');
define('smarty_language', 'DE');


?>