<?php
defined('_SECURE_') or die('Forbidden');
if(!auth_isadmin()){auth_block();};

include $apps_path['plug']."/gateway/gnokii/config.php";

$gw = core_gateway_get();

if ($gw == $gnokii_param['name']) {
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
			<h2>"._('Manage gnokii')."</h2>
			<form action=index.php?app=menu&inc=gateway_gnokii&op=manage_save method=post>
			"._CSRF_FORM_."
			<table class=playsms-table>
				<tbody>
				<tr>
					<td class=label-sizer>"._('Gateway name')."</td><td>gnokii $status_active</td>
				</tr>
				<tr>
					<td>"._('Gnokii installation path')."</td><td><input type=text size=30 maxlength=250 name=up_path value=\"".$gnokii_param['path']."\"> "._hint(_('No trailing slash')." \"/\"")."</td>
				</tr>
				</tbody>
			</table>
			<p><input type=submit class=button value=\""._('Save')."\">
			</form>";
		$content .= _back('index.php?app=menu&inc=tools_gatewaymanager&op=gatewaymanager_list');
		echo $content;
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
		header("Location: index.php?app=menu&inc=gateway_gnokii&op=manage");
		exit();
		break;
	case "manage_activate":
		$db_query = "UPDATE "._DB_PREF_."_tblConfig_main SET c_timestamp='".mktime()."',cfg_gateway_module='gnokii'";
		$db_result = dba_query($db_query);
		$_SESSION['error_string'] = _('Gateway has been activated');
		header("Location: index.php?app=menu&inc=gateway_gnokii&op=manage");
		exit();
		break;
}

?>