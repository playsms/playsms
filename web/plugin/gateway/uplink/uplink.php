<?php
defined('_SECURE_') or die('Forbidden');
if(!isadmin()){forcenoaccess();};

include $apps_path['plug']."/gateway/uplink/config.php";

$gw = gateway_get();

if ($gw == $uplink_param['name']) {
	$status_active = "<span class=status_active />";
} else {
	$status_active = "<span class=status_inactive />";
}


switch ($op) {
	case "manage":
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
			<h2>"._('Manage uplink')."</h2>
			<form action=index.php?app=menu&inc=gateway_uplink&op=manage_save method=post>
			<table width=100%>
				<tbody>
				<tr>
					<td width=270>"._('Gateway name')."</td><td>uplink $status_active</td>
				</tr>
				<tr>
					<td>"._('Master URL')."</td><td><input type=text size=30 maxlength=250 name=up_master value=\"".$uplink_param['master']."\"></td>
				</tr>
				<tr>
					<td>"._('Additional URL parameter')."</td><td><input type=text size=30 maxlength=250 name=up_additional_param value=\"".$uplink_param['additional_param']."\"></td>
				</tr>
				<tr>
					<td>"._('Webservice username')."</td><td><input type=text size=30 maxlength=30 name=up_username value=\"".$uplink_param['username']."\"></td>
				</tr>
				<tr>
					<td>"._('Webservice token')."</td><td><input type=text size=30 maxlength=32 name=up_token value=\"\"></td>
				</tr>
				<tr>
					<td>"._('Module sender ID')."</td><td><input type=text size=30 maxlength=16 name=up_global_sender value=\"".$uplink_param['global_sender']."\"> "._hint('Max. 16 numeric or 11 alphanumeric char. empty to disable')."</td>
				</tr>
				<tr>
					<td>"._('Module timezone')."</td><td><input type=text size=5 maxlength=5 name=up_global_timezone value=\"".$uplink_param['datetime_timezone']."\"> "._hint('Eg: +0700 for Jakarta/Bangkok timezone')."</td>
				</tr>
				</tbody>
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