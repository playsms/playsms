<?php
defined('_SECURE_') or die('Forbidden');
if(!auth_isadmin()){auth_block();};

include $core_config['apps_path']['plug']."/gateway/gnokii/config.php";

switch (_OP_) {
	case "manage":
		if ($err = TRUE) {
			$content = _dialog();
		}
		$content .= "
			<h2>"._('Manage gnokii')."</h2>
			<form action=index.php?app=main&inc=gateway_gnokii&op=manage_save method=post>
			"._CSRF_FORM_."
			<table class=playsms-table>
				<tbody>
				<tr>
					<td class=label-sizer>"._('Gateway name')."</td><td>gnokii</td>
				</tr>
				<tr>
					<td>"._('Gnokii installation path')."</td><td><input type=text maxlength=250 name=up_path value=\"".$plugin_config['gnokii']['path']."\"> "._hint(_('No trailing slash')." \"/\"")."</td>
				</tr>
				</tbody>
			</table>
			<p><input type=submit class=button value=\""._('Save')."\">
			</form>";
		$content .= _back('index.php?app=main&inc=core_gateway&op=gateway_list');
		_p($content);
		break;
	case "manage_save":
		$up_path = $_POST['up_path'];
		$_SESSION['dialog']['info'][] = _('No changes have been made');
		if ($up_path) {
			$db_query = "
				UPDATE "._DB_PREF_."_gatewayGnokii_config
				SET c_timestamp='".mktime()."',cfg_path='$up_path'";
			if (@dba_affected_rows($db_query)) {
				$_SESSION['dialog']['info'][] = _('Gateway module configurations has been saved');
			}
		}
		header("Location: "._u('index.php?app=main&inc=gateway_gnokii&op=manage'));
		exit();
		break;
}
