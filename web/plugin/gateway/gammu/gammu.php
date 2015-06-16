<?php
defined('_SECURE_') or die('Forbidden');
if (!auth_isadmin()) {
	auth_block();
}
;

include $core_config['apps_path']['plug'] . "/gateway/gammu/config.php";

switch (_OP_) {
	case "manage":
		if ($err = TRUE) {
			$content = _dialog();
		}
		$content .= "
			<h2>" . _('Manage gammu') . "</h2>
			<form action=index.php?app=main&inc=gateway_gammu&op=manage_save method=post>
			" . _CSRF_FORM_ . "
			<table class=playsms-table>
				<tbody>
				<tr>
					<td class=label-sizer>" . _('Gateway name') . "</td><td>gammu</td>
				</tr>
				<tr>
					<td>" . _('Spool folder') . "</td><td><input type=text name=up_path value=\"" . $plugin_config['gammu']['path'] . "\"></td>
				</tr>
				</tbody>
			</table>
			<p><input type=submit class=button value=\"" . _('Save') . "\">
			</form>";
		$content .= _back('index.php?app=main&inc=core_gateway&op=gateway_list');
		_p($content);
		break;
	case "manage_save":
		$up_path = core_sanitize_path($_POST['up_path']);
		$items = array(
			'path' => $up_path 
		);
		registry_update(0, 'gateway', 'gammu', $items);
		
		$_SESSION['dialog']['info'][] = _('Changes have been made');
		header("Location: " . _u('index.php?app=main&inc=gateway_gammu&op=manage'));
		exit();
		break;
}
