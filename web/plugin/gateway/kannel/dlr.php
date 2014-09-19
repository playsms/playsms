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
if (!$called_from_hook_call) {
	chdir("../../../");
	
	// ignore CSRF
	$core_config['init']['ignore_csrf'] = TRUE;
	
	include "init.php";
	include $core_config['apps_path']['libs'] . "/function.php";
	chdir("plugin/gateway/kannel");
	$requests = $_REQUEST;
}

$remote_addr = $_SERVER['REMOTE_ADDR'];
// srosa 20100531: added var below
$remote_host = $_SERVER['HTTP_HOST'];
// srosa 20100531: changed test below to allow hostname in bearerbox_host instead of ip
// if ($remote_addr != $plugin_config['kannel']['bearerbox_host'])
if ($remote_addr != $plugin_config['kannel']['bearerbox_host'] && $remote_host != $plugin_config['kannel']['bearerbox_host']) {
	logger_print("exit remote_addr:" . $remote_addr . " remote_host:" . $remote_host . " bearerbox_host:" . $plugin_config['kannel']['bearerbox_host'], 2, "kannel dlr");
	exit();
}

$type = $requests['type'];
$smslog_id = $requests['smslog_id'];
$uid = $requests['uid'];

logger_print("addr:" . $remote_addr . " host:" . $remote_host . " type:" . $type . " smslog_id:" . $smslog_id . " uid:" . $uid, 2, "kannel dlr");

if ($type && $smslog_id && $uid) {
	$stat = 0;
	switch ($type) {
		case 1 :
			$stat = 6;
			break; // delivered to phone = delivered
		case 2 :
			$stat = 5;
			break; // non delivered to phone = failed
		case 4 :
			$stat = 3;
			break; // queued on SMSC = pending
		case 8 :
			$stat = 4;
			break; // delivered to SMSC = sent
		case 16 :
			$stat = 5;
			break; // non delivered to SMSC = failed
		case 9 :
			$stat = 4;
			break; // sent
		case 12 :
			$stat = 4;
			break; // sent
		case 18 :
			$stat = 5;
			break; // failed
	}
	$p_status = $stat;
	if ($stat) {
		$p_status = $stat - 3;
	}
	dlr($smslog_id, $uid, $p_status);
}
