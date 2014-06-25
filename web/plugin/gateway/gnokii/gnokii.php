<?php
defined('_SECURE_') or die('Forbidden');
if(!auth_isadmin()){auth_block();};

include $core_config['apps_path']['plug']."/gateway/gnokii/config.php";

$gw = core_gateway_get();

if ($gw == $plugin_config['gnokii']['name']) {
	$status_active = "<span class=status_active />";
} else {
	$status_active = "<span class=status_inactive />";
}

switch (_OP_) {
	case "manage":
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
			<h2>"._('Manage gnokii')."</h2>
			<form action=index.php?app=main&inc=gateway_gnokii&op=manage_save method=post>
			"._CSRF_FORM_."
			<table class=playsms-table>
				<tbody>
				<tr>
					<td class=label-sizer>"._('Gateway name')."</td><td>gnokii $status_active</td>
				</tr>
				<tr>
					<td>"._('Gnokii installation path')."</td><td><input type=text maxlength=250 name=up_path value=\"".$plugin_config['gnokii']['path']."\"> "._hint(_('No trailing slash')." \"/\"")."</td>
				</tr>
				</tbody>
			</table>
			<p><input type=submit class=button value=\""._('Save')."\">
			</form>";
		$content .= _back('index.php?app=main&inc=feature_gatewaymanager&op=gatewaymanager_list');
		_p($content);
		break;
	case "manage_save":
		$up_path = $_POST['up_path'];
		$_SESSION['error_string'] = _('No changes has been made');
		if ($up_path) {
			$db_query = "
				UPDATE "._DB_PREF_."_gatewayGnokii_config 
				SET c_timestamp='".mktime()."',cfg_path='$up_path'";
			if (@dba_affected_rows($db_query)) {
				$_SESSION['error_string'] = _('Gateway module configurations has been saved');
			}
		}
		header("Location: "._u('index.php?app=main&inc=gateway_gnokii&op=manage'));
		exit();
		break;
}
