<?php
defined('fsr_tool') or die;

require_once("MiningMax.db.conf.php");
require_once("MiningMax.world.class.php");
# require_once("MiningMax.database.class.php"); alt = tot
require_once("MiningMax.database.miningbuddy.class.php");

# alte DB Verbindung vom MB:
require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/makedb.php');


# Verbindung zu _eveOrder
# require_once("../eveorder/classes/db.conf.php");
# require_once("../eveorder/classes/database.class.php");
# require_once("MiningMax.database.miningbuddy.class.php");



# Verbindung zu Snowflake MemberDB
# require_once("member.database.class.php");
# Snowflake DB stuff
# require_once('conf.member.db.php');
# require_once('class.member.db.php');


# classes aus MB
require_once("user_class.php");
require_once("solarSystem_class.php");
require_once("transaction_class.php");


# Functionen für MiningDivSkills
require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/MiningDivSkills.php');

# Funktionen aus dem MB
require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/makeNotice.php');
require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/numericcheck.php');
// require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/numericCheckBool.php');

require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/auth.php');
require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/authVerify.php');
require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/confirm.php');
require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/humanTime.php');
#require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/authverify.php'); doppelt
require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/usernameToID.php');
require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/idToUsername.php');
require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/idToIcon.php');

require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/login/sanitize.php');

require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/admin/getConfig.php');
require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/admin/userExists.php');
require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/admin/addNewUser.php');

require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/runs/RunsListing.php');
require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/runs/miningRunOpen.php');
require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/runs/runIsLocked.php');
require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/runs/runSupervisor.php');
require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/runs/getLocationOfRun.php');
require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/runs/getTotalHaulRuns.php');
require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/runs/userInRun.php');
require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/runs/joinRun.php');
require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/runs/leaveRun.php');
require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/runs/makeNewRun.php');
require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/runs/deleteRun.php');
require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/runs/addRun.php');
require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/runs/listRun.php');
require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/runs/listProject.php'); // unbedingt NACH listrun wegen CalcMetall :)
require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/runs/kick.php');
require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/runs/addHaulPage.php');
require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/runs/addHaul.php');
require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/runs/endRun.php');
require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/runs/togglePay.php');
require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/runs/savecomment.php');
require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/html/highscore.php');
require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/Memberliste.php');


require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/html/showOreValue.php');

# Berechnungssachen aus dem MB
require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/math/numberToString.php');
require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/getOreSettings.php');
require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/getTotalWorth.php');
require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/math/calcPayoutPercent.php');
// TODO require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/math/makeEmailReceipt.php');
require_once ( MODULE_DIR . ACTIVE_MODULE . '/functions/math/addCredit.php');


?>