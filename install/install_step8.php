<?php
/**
 * @package EDK
 */

global $smarty;

//Block further attempts to run the install in case the installer
// forgets to delete the install folder.
touch("install.lock");
session_destroy();
$smarty->display('install_step8.tpl');