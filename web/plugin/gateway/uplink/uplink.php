<?php

defined('_SECURE_') or die('Forbidden');
if (!isadmin()) {
	forcenoaccess();
};

include $apps_path['plug'] . "/gateway/uplink/config.php";

$gw = core_gateway_get();

if ($gw == $uplink_param['name']) {
	$status_active = "<span class=status_active />";
} else {
	$status_active = "<span class=status_inactive />";
}


switch ($op) {
	case "manage":
		if ($uplink_param['try_disable_footer']) {
			$selected['yes'] = 'selected';
		} else {
			$selected['no'] = 'selected';
		}
		$option_try_disable_footer = "<option value=\"1\" " . $selected['yes'] . ">" . _('yes') . "</option>";
		$option_try_disable_footer .= "<option value=\"0\" " . $selected['no'] . ">" . _('no') . "</option>";
		if ($err = $_SESSION['error_string']) {
			$error_content = "<div class=error_string>$err</div>";
		}
		$content = "
			" . $error_content . "
			<h2>" . _('Manage uplink') . "</h2>
			<form action=index.php?app=menu&inc=gateway_uplink&op=manage_save method=post>
			<table class=playsms-table>
				<tbody>
				<tr>
					<td class=label-sizer>" . _('Gateway name') . "</td><td>uplink $status_active</td>
				</tr>
				<tr>
					<td>" . _('Master URL') . "</td><td><input type=text size=30 maxlength=250 name=up_master value=\"" . $uplink_param['master'] . "\"></td>
				</tr>
				<tr>
					<td>" . _('Additional URL parameter') . "</td><td><input type=text size=30 maxlength=250 name=up_additional_param value=\"" . $uplink_param['additional_param'] . "\"></td>
				</tr>
				<tr>
					<td>" . _('Webservice username') . "</td><td><input type=text size=30 maxlength=30 name=up_username value=\"" . $uplink_param['username'] . "\"></td>
				</tr>
				<tr>
					<td>" . _('Webservice token') . "</td><td><input type=text size=30 maxlength=32 name=up_token value=\"\"></td>
				</tr>
				<tr>
					<td>" . _('Module sender ID') . "</td><td><input type=text size=30 maxlength=16 name=up_global_sender value=\"" . $uplink_param['global_sender'] . "\"> " . _hint(_('Max. 16 numeric or 11 alphanumeric char. empty to disable')) . "</td>
				</tr>
				<tr>
					<td>" . _('Try to disable SMS footer on master') . "</td><td><select name=up_try_disable_footer>" . $option_try_disable_footer . "</select></td>
				</tr>
				<tr>
					<td>" . _('Module timezone') . "</td><td><input type=text size=5 maxlength=5 name=up_global_timezone value=\"" . $uplink_param['datetime_timezone'] . "\"> " . _hint(_('Eg: +0700 for Jakarta/Bangkok timezone')) . "</td>
				</tr>
				</tbody>
			</table>
			<p><input type=submit class=button value=\"" . _('Save') . "\">
			</form>";
		$content .= _b('index.php?app=menu&inc=tools_gatewaymanager&op=gatewaymanager_list');
		echo $content;
		break;
	case "manage_save":
		$up_master = $_POST['up_master'];
		$up_additional_param = $_POST['up_additional_param'];
		$up_username = $_POST['up_username'];
		if ($up_token = $_POST['up_token']) {
			$update_token = "cfg_token='" . $up_token . "',";
		}
		$up_global_sender = $_POST['up_global_sender'];
		$up_global_timezone = $_POST['up_global_timezone'];
		$up_try_disable_footer = $_POST['up_try_disable_footer'];
		$_SESSION['error_string'] = _('No changes has been made');
		if ($up_master && $up_username) {
			$db_query = "
				UPDATE " . _DB_PREF_ . "_gatewayUplink_config
				SET c_timestamp='" . mktime() . "',
				cfg_master='$up_master',
				cfg_additional_param='$up_additional_param',
				cfg_username='$up_username',
				" . $update_token . "
				cfg_global_sender='$up_global_sender',
				cfg_datetime_timezone='$up_global_timezone',
				cfg_try_disable_footer='$up_try_disable_footer'";
			if (@dba_affected_rows($db_query)) {
				$_SESSION['error_string'] = _('Gateway module configurations has been saved');
			}
		}
		header("Location: index.php?app=menu&inc=gateway_uplink&op=manage");
		exit();
		break;
	case "manage_activate":
		$db_query = "UPDATE " . _DB_PREF_ . "_tblConfig_main SET c_timestamp='" . mktime() . "',cfg_gateway_module='uplink'";
		$db_result = dba_query($db_query);
		$_SESSION['error_string'] = _('Gateway has been activated');
		header("Location: index.php?app=menu&inc=gateway_uplink&op=manage");
		exit();
		break;
}
?>