<?php

defined('_SECURE_') or die('Forbidden');
if (!auth_isadmin()) {
	auth_block();
};

include $core_config['apps_path']['plug'] . "/gateway/uplink/config.php";

switch (_OP_) {
	case "manage":
		if ($plugin_config['uplink']['try_disable_footer']) {
			$selected['yes'] = 'selected';
		} else {
			$selected['no'] = 'selected';
		}
		$option_try_disable_footer = "<option value=\"1\" " . $selected['yes'] . ">" . _('yes') . "</option>";
		$option_try_disable_footer .= "<option value=\"0\" " . $selected['no'] . ">" . _('no') . "</option>";
		if ($err = TRUE) {
			$error_content = _dialog();
		}
		$content = "
			" . $error_content . "
			<h2>" . _('Manage uplink') . "</h2>
			<form action=index.php?app=main&inc=gateway_uplink&op=manage_save method=post>
			"._CSRF_FORM_."
			<table class=playsms-table>
				<tbody>
				<tr>
					<td class=label-sizer>" . _('Gateway name') . "</td><td>uplink</td>
				</tr>
				<tr>
					<td>" . _('Master URL') . "</td><td><input type=text maxlength=250 name=up_master value=\"" . $plugin_config['uplink']['master'] . "\"></td>
				</tr>
				<tr>
					<td>" . _('Additional URL parameter') . "</td><td><input type=text maxlength=250 name=up_additional_param value=\"" . $plugin_config['uplink']['additional_param'] . "\"></td>
				</tr>
				<tr>
					<td>" . _('Webservice username') . "</td><td><input type=text maxlength=30 name=up_username value=\"" . $plugin_config['uplink']['username'] . "\"></td>
				</tr>
				<tr>
					<td>" . _('Webservice token') . "</td><td><input type=text maxlength=32 name=up_token value=\"\"></td>
				</tr>
				<tr>
					<td>" . _('Module sender ID') . "</td><td><input type=text maxlength=16 name=up_module_sender value=\"" . $plugin_config['uplink']['module_sender'] . "\"> " . _hint(_('Max. 16 numeric or 11 alphanumeric char. empty to disable')) . "</td>
				</tr>
				<tr>
					<td>" . _('Try to disable SMS footer on master') . "</td><td><select name=up_try_disable_footer>" . $option_try_disable_footer . "</select></td>
				</tr>
				<tr>
					<td>" . _('Module timezone') . "</td><td><input type=text size=5 maxlength=5 name=up_global_timezone value=\"" . $plugin_config['uplink']['datetime_timezone'] . "\"> " . _hint(_('Eg: +0700 for Jakarta/Bangkok timezone')) . "</td>
				</tr>
				</tbody>
			</table>
			<p><input type=submit class=button value=\"" . _('Save') . "\">
			</form>";
		$content .= _back('index.php?app=main&inc=core_gateway&op=gateway_list');
		_p($content);
		break;
	case "manage_save":
		$up_master = $_POST['up_master'];
		$up_additional_param = $_POST['up_additional_param'];
		$up_username = $_POST['up_username'];
		if ($up_token = $_POST['up_token']) {
			$update_token = "cfg_token='" . $up_token . "',";
		}
		$up_module_sender = $_POST['up_module_sender'];
		$up_global_timezone = $_POST['up_global_timezone'];
		$up_try_disable_footer = $_POST['up_try_disable_footer'];
		$_SESSION['dialog']['info'][] = _('No changes have been made');
		if ($up_master && $up_username) {
			$db_query = "
				UPDATE " . _DB_PREF_ . "_gatewayUplink_config
				SET c_timestamp='" . mktime() . "',
				cfg_master='$up_master',
				cfg_additional_param='$up_additional_param',
				cfg_username='$up_username',
				" . $update_token . "
				cfg_module_sender='$up_module_sender',
				cfg_datetime_timezone='$up_global_timezone',
				cfg_try_disable_footer='$up_try_disable_footer'";
			if (@dba_affected_rows($db_query)) {
				$_SESSION['dialog']['info'][] = _('Gateway module configurations has been saved');
			}
		}
		header("Location: "._u('index.php?app=main&inc=gateway_uplink&op=manage'));
		exit();
		break;
}
