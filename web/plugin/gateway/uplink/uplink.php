<?php
defined('_SECURE_') or die('Forbidden');
if(!isadmin()){forcenoaccess();};

include $apps_path['plug']."/gateway/uplink/config.php";

$gw = gateway_get();

if ($gw == $uplink_param['name']) {
	$status_active = "(<b><font color=green>"._('Active')."</font></b>)";
} else {
	$status_active = "(<b><font color=red>"._('Inactive')."</font></b>) (<a href=\"index.php?app=menu&inc=gateway_uplink&op=manage_activate\">"._('click here to activate')."</a>)";
}


switch ($op) {
	case "manage":
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
			<h2>"._('Manage uplink')."</h2>
			<p>
			<form action=index.php?app=menu&inc=gateway_uplink&op=manage_save method=post>
			<table width=100% cellpadding=1 cellspacing=2 border=0>
			<tr>
				<td width=200>"._('Gateway name')."</td><td width=5>:</td><td><b>uplink</b> $status_active</td>
			</tr>
			<tr>
				<td>"._('Master URL')."</td><td>:</td><td><input type=text size=30 maxlength=250 name=up_master value=\"".$uplink_param['master']."\"></td>
			</tr>
			<tr>
				<td>"._('Additional URL parameter')."</td><td>:</td><td><input type=text size=30 maxlength=250 name=up_additional_param value=\"".$uplink_param['additional_param']."\"></td>
			</tr>
			<tr>
				<td>"._('Webservice username')."</td><td>:</td><td><input type=text size=30 maxlength=30 name=up_username value=\"".$uplink_param['username']."\"></td>
			</tr>
			<tr>
				<td>"._('Webservice token')."</td><td>:</td><td><input type=text size=30 maxlength=32 name=up_token value=\"\"></td>
			</tr>
			<tr>
				<td>"._('Module sender ID')."</td><td>:</td><td><input type=text size=30 maxlength=16 name=up_global_sender value=\"".$uplink_param['global_sender']."\"> ("._('Max. 16 numeric or 11 alphanumeric char. empty to disable').")</td>
			</tr>
			<tr>
				<td>"._('Module timezone')."</td><td>:</td><td><input type=text size=5 maxlength=5 name=up_global_timezone value=\"".$uplink_param['datetime_timezone']."\"> ("._('Eg: +0700 for Jakarta/Bangkok timezone').")</td>
			</tr>
			</table>
			<p><input type=submit class=button value=\""._('Save')."\">
			</form>";
		echo $content;
		break;
	case "manage_save":
		$up_master = $_POST['up_master'];
		$up_additional_param = $_POST['up_additional_param'];
		$up_username = $_POST['up_username'];
		if ($up_token = $_POST['up_token']) {
			$update_token = "cfg_token='".$up_token."',";
		}
		$up_global_sender = $_POST['up_global_sender'];
		$up_global_timezone = $_POST['up_global_timezone'];
		$up_incoming_path = $_POST['up_incoming_path'];
		$_SESSION['error_string'] = _('No changes has been made');
		if ($up_master && $up_username && $up_token) {
			$db_query = "
				UPDATE "._DB_PREF_."_gatewayUplink_config 
				SET c_timestamp='".mktime()."',
				cfg_master='$up_master',
				cfg_additional_param='$up_additional_param',
				cfg_username='$up_username',
				".$update_token."
				cfg_global_sender='$up_global_sender',
				cfg_datetime_timezone='$up_global_timezone',
				cfg_incoming_path='$up_incoming_path'";
			if (@dba_affected_rows($db_query)) {
				$_SESSION['error_string'] = _('Gateway module configurations has been saved');
			}
		}
		header("Location: index.php?app=menu&inc=gateway_uplink&op=manage");
		exit();
		break;
	case "manage_activate":
		$db_query = "UPDATE "._DB_PREF_."_tblConfig_main SET c_timestamp='".mktime()."',cfg_gateway_module='uplink'";
		$db_result = dba_query($db_query);
		$_SESSION['error_string'] = _('Gateway has been activated');
		header("Location: index.php?app=menu&inc=gateway_uplink&op=manage");
		exit();
		break;
}

?>