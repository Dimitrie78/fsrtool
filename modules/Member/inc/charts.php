<?php
$now  = time();
$day1 = strtotime ("-1 day 10:00"));
$day2 = strtotime ("-2 day 10:00"));
$day3 = strtotime ("-3 day 10:00"));
$day4 = strtotime ("-4 day 10:00"));
$day5 = strtotime ("-5 day 10:00"));
$day6 = strtotime ("-6 day 10:00"));
$day7 = strtotime ("-7 day 10:00"));

require_once("../classes/conf.member.db.php");
//$corpID = addslashes($_GET['corpID']);
$conn = mysql_connect(db_host_fsrclan_member, db_user_fsrclan_member, db_pass_fsrclan_member)or die(mysql_error());
mysql_select_db(db_name_fsrclan_member,$conn) or die(mysql_error());

$result = mysql_query("SELECT Sum(amount) as amount FROM walletjournal WHERE refTypeID = 85 AND date between {$now} AND {$day1};");
$row = mysql_fetch_assoc($result);
$res = $row['amount'];
mysql_free_result($result);

$result = mysql_query("SELECT Sum(amount) as amount FROM walletjournal WHERE refTypeID = 85 AND date between {$day1} AND {$day2};");
$row = mysql_fetch_assoc($result);
$res += ','.$row['amount'];
mysql_free_result($result);

$result = mysql_query("SELECT Sum(amount) as amount FROM walletjournal WHERE refTypeID = 85 AND date between {$day2} AND {$day3};");
$row = mysql_fetch_assoc($result);
$res += ','.$row['amount'];
mysql_free_result($result);

$result = mysql_query("SELECT Sum(amount) as amount FROM walletjournal WHERE refTypeID = 85 AND date between {$day3} AND {$day4};");
$row = mysql_fetch_assoc($result);
$res += ','.$row['amount'];
mysql_free_result($result);

$result = mysql_query("SELECT Sum(amount) as amount FROM walletjournal WHERE refTypeID = 85 AND date between {$day4} AND {$day5};");
$row = mysql_fetch_assoc($result);
$res += ','.$row['amount'];
mysql_free_result($result);

$result = mysql_query("SELECT Sum(amount) as amount FROM walletjournal WHERE refTypeID = 85 AND date between {$day5} AND {$day6};");
$row = mysql_fetch_assoc($result);
$res += ','.$row['amount'];
mysql_free_result($result);

$result = mysql_query("SELECT Sum(amount) as amount FROM walletjournal WHERE refTypeID = 85 AND date between {$day6} AND {$day7};");
$row = mysql_fetch_assoc($result);
$res += ','.$row['amount'];
mysql_free_result($result);

mysql_close($conn);

?>