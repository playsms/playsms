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
error_reporting(0);

if (!$called_from_hook_call) {
	chdir("../../../");
	
	// ignore CSRF
	$core_config['init']['ignore_csrf'] = TRUE;
	
	include "init.php";
	include $core_config['apps_path']['libs'] . "/function.php";
	chdir("plugin/gateway/openvox/");
	$requests = $_REQUEST;
}

$log = '';
if (is_array($requests)) {
	foreach ($requests as $key => $val) {
		$log .= $key . ':' . $val . ' ';
	}
	logger_print("pushed " . $log, 2, "openvox callback");
}

// incoming message
$sms_datetime = core_get_datetime();
$sms_sender = $requests['phonenumber'];
$message = htmlspecialchars_decode(urldecode($requests['message']));
$sms_receiver = core_sanitize_sender($requests['port']);
$smsc = $requests['smsc'];
if ($message) {
	logger_print("incoming smsc:" . $smsc . " from:" . $sms_sender . " port:" . $sms_receiver . " m:[" . $message . "] smsc:[" . $smsc . "]", 2, "openvox callback");
	recvsms($sms_datetime, $sms_sender, $message, $sms_receiver, $smsc);
}
