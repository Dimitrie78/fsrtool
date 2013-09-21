<?php

if( isset($_POST['charID']) && is_numeric($_POST['charID']) ){  // Post Variable Input Feld ?
	require_once("../classes/conf.member.db.php");
	//$corpID = addslashes($_GET['corpID']);
	$conn = mysql_connect(db_host_fsrclan_member, db_user_fsrclan_member, db_pass_fsrclan_member)or die(mysql_error());
	mysql_select_db(db_name_fsrclan_member,$conn) or die(mysql_error());
	
	$sql = "SELECT w.date, w.amount, map.itemName
	FROM walletjournal w 
	INNER JOIN mapdenormalize map ON w.argID1 = map.itemID
	WHERE w.ownerID2 = '".mysql_real_escape_string($_POST['charID'])."' 
	AND w.refTypeID = 85
	ORDER BY w.date DESC
	LIMIT 20;";
	
	$return_array = array();

	$result=mysql_query($sql);
	echo  '<table width="100%">'
		 .'<tr><td colspan="3" width="100%" style="text-align: right; vertical-align: top">'
		 .'<a href="javascript:hideDiv(\'isAltWin\')">'
		 .'<img src="modules/Member/images/greenx.jpg"></a></td></tr>';
	echo "<tr><th>Date</th><th>amount</th><th>System</th></tr>\n";
	while($row=mysql_fetch_assoc($result)){
		echo "<tr><td align=\"center\">".date('d-m H:i',$row['date'])."</td><td align=\"right\">".number_format($row['amount'],2,',','.')."</td><td align=\"center\">{$row['itemName']}</td></tr>\n";
	}
	echo "</table>\n";
	mysql_free_result($result);
	mysql_close($conn);
}
?>