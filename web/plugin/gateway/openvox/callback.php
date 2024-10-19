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

error_reporting(0);

// load callback init
if ($callback_init = _APPS_PATH_PLUG_ . '/gateway/common/callback_init.php' && is_file($callback_init)) {
	include $callback_init;
}

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
