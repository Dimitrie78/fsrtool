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

########################################################################
# mySQL-Tabellen

#define('db_tab_config',			 TBL_PREFIX'config');

/* define('PREFIX', 	 TBL_PREFIX);
define('PREFIX_EVE', TBL_PREFIX.'eve_');

define('db_tab_user',			 TBL_PREFIX.'user');
define('db_tab_user_online',	 TBL_PREFIX.'user_online');
define('db_tab_user_fullapi',	 TBL_PREFIX.'user_fullapi');
define('db_tab_user_iplog',		 TBL_PREFIX.'user_iplog');
define('db_tab_alts',			 TBL_PREFIX.'user_alts');
define('db_tab_user_roles', 	 TBL_PREFIX.'user_roles');
define('db_tab_log',			 TBL_PREFIX.'api_log');
define('db_tab_allys',			 TBL_PREFIX.'allys');
define('db_tab_corps',			 TBL_PREFIX.'corps');
define('db_tab_corpchange',		 TBL_PREFIX.'corpchange');
define('db_tab_currentTypePrice',TBL_PREFIX.'currenttypeprice');
define('db_tab_logins',			 TBL_PREFIX.'logins');
define('db_tab_roles',			 TBL_PREFIX.'roles');
define('db_tab_cron',			 TBL_PREFIX.'cron');

# Tower Tabellen
define('db_tab_pos',		 	 TBL_PREFIX.'pos');
define('db_tab_pos_fuel',		 TBL_PREFIX.'pos_fuel');
define('db_tab_corphanger',		 TBL_PREFIX.'pos_corphanger');
define('db_tab_fuelFilter',		 TBL_PREFIX.'pos_filter');

# Silos Tabellen
define('db_tab_silos',	 		  TBL_PREFIX.'silos');
define('db_tab_silos_reactions',  TBL_PREFIX.'silos_reactions');
define('db_tab_silos_reactors',   TBL_PREFIX.'silos_reactors');
define('db_tab_silos_cachetimes', TBL_PREFIX.'silos_cachetimes');

# Api Tabellen
define('db_tab_sovereignty',	 TBL_PREFIX.'api_sovereignty');
define('db_tab_outposts',	 	 TBL_PREFIX.'api_outposts');
define('db_tab_refTypes', 	 	 TBL_PREFIX.'api_reftypes');

# EvE Tabllen
define('db_tab_dgmtypeattributes', 		TBL_PREFIX.'eve_dgmtypeattributes');
define('db_tab_towerresourcepurposes', 	TBL_PREFIX.'eve_invcontroltowerresourcepurposes');
define('db_tab_towerresources',	 		TBL_PREFIX.'eve_invcontroltowerresources');
define('db_tab_invtypes',		 		TBL_PREFIX.'eve_invtypes');
define('db_tab_invtypereactions',		TBL_PREFIX.'eve_invtypereactions');
define('db_tab_mapconstellations',		TBL_PREFIX.'eve_mapconstellations');
define('db_tab_mapregions',		 		TBL_PREFIX.'eve_mapregions');
define('db_tab_mapdenormalize',			TBL_PREFIX.'eve_mapdenormalize');
define('db_tab_mapsolarsystems', 		TBL_PREFIX.'eve_mapsolarsystems');
define('db_tab_stastations',			TBL_PREFIX.'eve_stastations');

define('db_tab_invmarketgroups',		TBL_PREFIX.'eve_invmarketgroups');
define('db_tab_eveicons',				TBL_PREFIX.'eve_eveicons');
define('db_tab_invgroups',				TBL_PREFIX.'eve_invgroups');
define('db_tab_invblueprinttypes', 		TBL_PREFIX.'eve_invblueprinttypes');
define('db_tab_trntranslations', 		TBL_PREFIX.'eve_trntranslations'); */

?>