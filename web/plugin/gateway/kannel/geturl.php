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
}

$remote_addr = $_SERVER['REMOTE_ADDR'];
// srosa 20100531: added var below
$remote_host = $_SERVER['HTTP_HOST'];
// srosa 20100531: changed test below to allow hostname in bearerbox_host instead of ip
// if ($remote_addr != $plugin_config['kannel']['bearerbox_host'])
if ($remote_addr != $plugin_config['kannel']['bearerbox_host'] && $remote_host != $plugin_config['kannel']['bearerbox_host']) {
	logger_print("exit remote_addr:" . $remote_addr . " remote_host:" . $remote_host . " bearerbox_host:" . $plugin_config['kannel']['bearerbox_host'], 2, "kannel incoming");
	exit();
}

// if the arrival time is in UTC then we need to adjust it with this:
if ($plugin_config['kannel']['local_time']) {
	$t = trim($_REQUEST['t']);
} else {
	// in UTC
	$t = core_display_datetime($_REQUEST['t']);
}

$q = trim($_REQUEST['q']); // sms_sender
$a = trim(htmlspecialchars_decode($_REQUEST['a'])); // message
$Q = trim($_REQUEST['Q']); // sms_receiver
$smsc = trim($_REQUEST['smsc']); // SMSC

logger_print("addr:" . $remote_addr . " host:" . $remote_host . " t:" . $t . " q:" . $q . " a:" . $a . " Q:" . $Q . " smsc:[" . $smsc . "]", 3, "kannel incoming");

if ($t && $q && $a) {
	// collected:
	// $sms_datetime, $sms_sender, $message, $sms_receiver
	$q = addslashes($q);
	$a = addslashes($a);
	recvsms($t, $q, $a, $Q, $smsc);
}
