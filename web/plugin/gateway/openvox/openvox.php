<?php

/**
 * This file is part of playSMS.
 *
 * playSMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * playSMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with playSMS. If not, see <http://www.gnu.org/licenses/>.
 */
defined('_SECURE_') or die('Forbidden');

if (!auth_isadmin()) {
	auth_block();
}

include $core_config['apps_path']['plug'] . "/gateway/openvox/config.php";

$callback_url = $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/plugin/gateway/openvox/callback.php";
$callback_url = str_replace("//", "/", $callback_url);
$callback_url = "http://" . $callback_url;

switch (_OP_) {
	case "manage":
		if ($err = TRUE) {
			$error_content = _dialog();
		}
		$tpl = array(
			'name' => 'openvox',
			'vars' => array(
				'DIALOG_DISPLAY' => $error_content,
				'Manage OpenVox' => _('Manage OpenVox'),
				'Gateway name' => _('Gateway name'),
				'Gateway host' => _('Gateway host'),
				'Gateway port' => _('Gateway port'),
				'Username' => _('Username'),
				'Password' => _('Password'),
				'Module sender ID' => _('Module sender ID'),
				'Module timezone' => _('Module timezone'),
				'Save' => _('Save'),
				'Notes' => _('Notes'),
				'HINT_FILL_SECRET' => _hint(_('Fill to change the password')),
				'CALLBACK_URL_IS' => _('Your callback URL is'),
				'CALLBACK_URL_ACCESSIBLE' => _('Your callback URL should be accessible from OpenVox'),
				'BUTTON_BACK' => _back('index.php?app=main&inc=core_gateway&op=gateway_list'),
				'openvox_param_gateway_host' => $plugin_config['openvox']['gateway_host'],
				'openvox_param_gateway_port' => $plugin_config['openvox']['gateway_port'],
				'openvox_param_username' => $plugin_config['openvox']['username'],
				'callback_url' => $callback_url 
			) 
		);
		_p(tpl_apply($tpl));
		break;
	case "manage_save":
		$_SESSION['dialog']['info'][] = _('Changes have been made');
		$items = array(
			'gateway_host' => $_POST['up_gateway_host'],
			'gateway_port' => $_POST['up_gateway_port'],
			'username' => $_POST['up_username'],
			'password' => $_POST['up_password']
		);
		if ($_POST['up_password']) {
			$items['password'] = $_POST['up_password'];
		}
		registry_update(1, 'gateway', 'openvox', $items);
		header("Location: " . _u('index.php?app=main&inc=gateway_openvox&op=manage'));
		exit();
		break;
}
