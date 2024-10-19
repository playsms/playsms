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

// set gateway name and log marker
define('_CALLBACK_GATEWAY_NAME_', 'openvox');
define('_CALLBACK_GATEWAY_LOG_MARKER_', _CALLBACK_GATEWAY_NAME_ . ' callback');
// -------------------- START OF CALLBACK INIT --------------------
error_reporting(0);
if (!(isset($do_not_reload_init) && $do_not_reload_init === true)) {
	if ($core_config['init']['cwd'] = getcwd()) {
		if (chdir('../../../')) {
			$core_config['init']['ignore_csrf'] = true; // ignore CSRF
			if (is_file('init.php')) { // load init && functions
				include 'init.php';
				if (isset($core_config['apps_path']['libs']) && $core_config['apps_path']['libs'] && is_file($core_config['apps_path']['libs'] . '/function.php')) {
					include $core_config['apps_path']['libs'] . '/function.php';
				}
			}
			if (!(function_exists('core_sanitize_alphanumeric') && function_exists('gateway_decide_smsc'))) { // double check
				exit();
			}
			if (!(isset($core_config['init']['cwd']) && chdir($core_config['init']['cwd']))) { // go back
				exit();
			}
		} else {
			exit();
		}
	} else {
		exit();
	}
}
$requests = $_REQUEST; // get web requests
$log = ''; // log pushed vars
if (is_array($requests)) {
	foreach ( $requests as $key => $val ) {
		$log .= $key . ':' . $val . ' ';
	}
	_log("pushed " . $log, 3, _CALLBACK_GATEWAY_LOG_MARKER_);
}
// -------------------- END OF CALLBACK INIT --------------------

// incoming message
$sms_datetime = core_display_datetime(core_get_datetime());
$sms_sender = $requests['phonenumber'];
$message = htmlspecialchars_decode(urldecode($requests['message']));
$sms_receiver = core_sanitize_sender($requests['port']);
$smsc = $requests['smsc'];
if ($message) {
	_log("incoming smsc:" . $smsc . " from:" . $sms_sender . " port:" . $sms_receiver . " m:[" . $message . "] smsc:[" . $smsc . "]", 2, _CALLBACK_GATEWAY_LOG_MARKER_);
	$sms_sender = addslashes($sms_sender);
	$message = addslashes($message);
	recvsms($sms_datetime, $sms_sender, $message, $sms_receiver, $smsc);
}
