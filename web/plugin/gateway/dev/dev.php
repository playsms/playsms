<?php
defined('_SECURE_') or die('Forbidden');
if(!isadmin()){forcenoaccess();};

include $apps_path['plug']."/gateway/dev/config.php";

$gw = gateway_get();

if ($gw == $dev_param['name']) {
	$status_active = "(<b><font color=green>"._('Active')."</font></b>)";
} else {
	$status_active = "(<b><font color=red>"._('Inactive')."</font></b>) (<a href=\"index.php?app=menu&inc=gateway_dev&op=manage_activate\">"._('click here to activate')."</a>)";
}

switch ($op) {
	case "manage":
		$sender = '+629876543210';
		$receiver = '1234';
		$datetime = core_get_datetime();
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
			<h2>"._('Manage dev')."</h2>
			<p>
			<table width=100% cellpadding=1 cellspacing=2 border=0>
			<tr><td width=200>"._('Gateway name')."</td><td width=5>:</td><td><b>dev</b> $status_active</td></tr>
			</table>
			<h3>"._('Simulate incoming SMS')."</h3>
			<form action=\"index.php?app=menu&inc=gateway_dev&op=simulate\" method=post>
			<table width=100% cellpadding=1 cellspacing=2 border=0>
			<tr><td width=200>"._('Message')."</td><td>:</td><td><input type=text name=message value=\"$message\" size=40 maxlength=250></td></tr>
			<tr><td>"._('Sender')."</td><td>:</td><td><input type=text name=sender value=\"$sender\" size=20 maxlength=20></td></tr>
			<tr><td>"._('Receiver')."</td><td>:</td><td><input type=text name=receiver value=\"$receiver\" size=20 maxlength=20></td></tr>
			<tr><td>"._('Date/Time')."</td><td>:</td><td><input type=text name=datetime value=\"$datetime\" size=20 maxlength=20></td></tr>
			</table>
			<p><input type=submit class=button value=\""._('Submit')."\">
			</form>";
		echo $content;
		break;
	case "manage_activate":
		$db_query = "UPDATE "._DB_PREF_."_tblConfig_main SET c_timestamp='".mktime()."',cfg_gateway_module='dev'";
		$db_result = dba_query($db_query);
		$_SESSION['error_string'] = _('Gateway has been activated');
		header("Location: index.php?app=menu&inc=gateway_dev&op=manage");
		exit();
		break;
	case "simulate":
		$sms_sender = ( $_REQUEST['sender'] ? $_REQUEST['sender'] : '+629876543210' );
		$sms_receiver = ( $_REQUEST['receiver'] ? $_REQUEST['receiver'] : '1234' );
		$sms_datetime = ( $_REQUEST['datetime'] ? $_REQUEST['datetime'] : core_get_datetime() );
		$message = ( $_REQUEST['message'] ? $_REQUEST['message'] : _('This is a test incoming SMS message') );
		if (trim($sms_sender) && trim($sms_receiver) && trim($sms_datetime) && trim($message)) {
			setsmsincomingaction($sms_datetime,$sms_sender,$message,$sms_receiver);
			$err = "sender:".$sms_sender." receiver:".$sms_receiver." dt:".$sms_datetime." msg:".stripslashes($message);
			logger_print($err, 3, "dev incoming");
			$_SESSION['error_string'] = $err;
		} else {
			$_SESSION['error_string'] = _('Fail to simulate incoming SMS');
		}
		header("Location: index.php?app=menu&inc=gateway_dev&op=manage");
		exit();
		break;
}
?>