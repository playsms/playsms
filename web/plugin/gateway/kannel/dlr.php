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
define('_CALLBACK_GATEWAY_NAME_', 'kannel');
define('_CALLBACK_GATEWAY_LOG_MARKER_', _CALLBACK_GATEWAY_NAME_ . ' dlr');

error_reporting(0);

// load callback init
if (!(isset($PLAYSMS_INIT_SKIP) && $PLAYSMS_INIT_SKIP === true) && is_file('../common/callback_init.php')) {
	include '../common/callback_init.php';
}

if ($smsc = $requests['smsc']) {
	// override plugin gateway configuration by smsc configuration
	$plugin_config = gateway_apply_smsc_config($smsc, $plugin_config);
}

$remote_addr = $_SERVER['REMOTE_ADDR'];
// srosa 20100531: added var below
$remote_host = $_SERVER['HTTP_HOST'];
// srosa 20100531: changed test below to allow hostname in bearerbox_host instead of ip
// if ($remote_addr != $plugin_config['kannel']['bearerbox_host'])
if ($remote_addr != $plugin_config['kannel']['bearerbox_host'] && $remote_host != $plugin_config['kannel']['bearerbox_host']) {
	_log("unable to process DLR. remote_addr:[" . $remote_addr . "] or remote_host:[" . $remote_host . "] does not match with your bearerbox_host config:[" . $plugin_config['kannel']['bearerbox_host'] . "] smsc:[" . $smsc . "]", 2, _CALLBACK_GATEWAY_LOG_MARKER_);
	exit();
}

$type = $requests['type'];
$smslog_id = $requests['smslog_id'];
$uid = $requests['uid'];

_log("remote_addr:" . $remote_addr . " remote_host:" . $remote_host . " type:[" . $type . "] smslog_id:[" . $smslog_id . "] uid:[" . $uid . "] smsc:[" . $smsc . "]", 3, _CALLBACK_GATEWAY_LOG_MARKER_);

if ($type && $smslog_id && $uid) {
	$stat = 0;
	switch ($type) {
		case 1:
			$stat = 6;
			break; // delivered to phone = delivered
		case 2:
			$stat = 5;
			break; // non delivered to phone = failed
		case 4:
			$stat = 3;
			break; // queued on SMSC = pending
		case 8:
			$stat = 4;
			break; // delivered to SMSC = sent
		case 16:
			$stat = 5;
			break; // non delivered to SMSC = failed
		case 9:
			$stat = 4;
			break; // sent
		case 12:
			$stat = 4;
			break; // sent
		case 18:
			$stat = 5;
			break; // failed
	}
	$p_status = $stat;
	if ($stat) {
		$p_status = $stat - 3;
	}
	dlr($smslog_id, $uid, $p_status);
} else {
	_log("missing parameter type:[" . $type . "] smslog_id:[" . $smslog_id . "] uid:[" . $uid . "] smsc:[" . $smsc . "]", 2, _CALLBACK_GATEWAY_LOG_MARKER_);
}
