<?php
defined('_SECURE_') or die('Forbidden');
if(!isadmin()){forcenoaccess();};

include $apps_path['plug']."/gateway/msgtoolbox/config.php";

$gw = core_gateway_get();

if ($gw == $msgtoolbox_param['name']) {
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
			<h2>"._('Manage msgtoolbox')."</h2>
			<form action=index.php?app=menu&inc=gateway_msgtoolbox&op=manage_save method=post>
			"._CSRF_FORM_."
			<table class=playsms-table>
				<tbody>
				<tr>
					<td class=label-sizer>"._('Gateway name')."</td><td>msgtoolbox $status_active</td>
				</tr>
				<tr>
					<td>"._('msgtoolbox URL')."</td><td><input type=text size=30 maxlength=250 name=up_url value=\"".$msgtoolbox_param['url']."\"></td>
				</tr>
				<tr>
					<td>"._('Route')."</td><td><input type=text size=5 maxlength=5 name=up_route value=\"".$msgtoolbox_param['route']."\"></td>
				</tr>
				<td>"._('Username')."</td><td><input type=text size=30 maxlength=30 name=up_username value=\"".$msgtoolbox_param['username']."\"></td>
				</tr>
				<tr>
					<td>"._('Password')."</td><td><input type=password size=30 maxlength=30 name=up_password value=\"\"> "._hint(_('Fill to change the password'))."</td>
				</tr>
				<tr>
					<td>"._('Module sender ID')."</td><td><input type=text size=30 maxlength=16 name=up_global_sender value=\"".$msgtoolbox_param['global_sender']."\"> "._hint(_('Max. 16 numeric or 11 alphanumeric char. empty to disable'))."</td>
				</tr>
				<tr>
					<td>"._('Module timezone')."</td><td><input type=text size=5 maxlength=5 name=up_global_timezone value=\"".$msgtoolbox_param['datetime_timezone']."\"> "._hint(_('Eg: +0700 for Jakarta/Bangkok timezone'))."</td>
				</tr>
				</tbody>
			</table>
			<p><input type=submit class=button value=\""._('Save')."\">
			</form>";
		$content .= _b('index.php?app=menu&inc=tools_gatewaymanager&op=gatewaymanager_list');
		echo $content;
		break;
	case "manage_save":
		$up_url = $_POST['up_url'];
		$up_route = $_POST['up_route'];
		$up_username = $_POST['up_username'];
		$up_password = $_POST['up_password'];
		$up_global_sender = $_POST['up_global_sender'];
		$up_global_timezone = $_POST['up_global_timezone'];
		$_SESSION['error_string'] = _('No changes has been made');
		if ($up_url && $up_username) {
			if ($up_password) {
				$password_change = "cfg_password='$up_password',";
			}
			$db_query = "
				UPDATE "._DB_PREF_."_gatewayMsgtoolbox_config 
				SET c_timestamp='".mktime()."',
				cfg_url='$up_url',
				cfg_route='$up_route',
				cfg_username='$up_username',
				".$password_change."
				cfg_global_sender='$up_global_sender',
				cfg_datetime_timezone='$up_global_timezone'
			";
			if (@dba_affected_rows($db_query)) {
				$_SESSION['error_string'] = _('Gateway module configurations has been saved');
			}
		}
		header("Location: index.php?app=menu&inc=gateway_msgtoolbox&op=manage");
		exit();
		break;
	case "manage_activate":
		$db_query = "UPDATE "._DB_PREF_."_tblConfig_main SET c_timestamp='".mktime()."',cfg_gateway_module='msgtoolbox'";
		$db_result = dba_query($db_query);
		$_SESSION['error_string'] = _('Gateway has been activated');
		header("Location: index.php?app=menu&inc=gateway_msgtoolbox&op=manage");
		exit();
		break;
}

?>