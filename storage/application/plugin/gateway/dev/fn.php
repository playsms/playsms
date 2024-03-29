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

function dev_hook_sendsms($smsc, $sms_sender, $sms_footer, $sms_to, $sms_msg, $uid = '', $gpid = 0, $smslog_id = 0, $sms_type = 'text', $unicode = 0)
{
	global $plugin_config;

	$ok = false;

	_log("enter smsc:" . $smsc . " smslog_id:" . $smslog_id . " uid:" . $uid . " to:" . $sms_to, 3, "dev_hook_sendsms");

	// override plugin gateway configuration by smsc configuration
	$plugin_config = gateway_apply_smsc_config($smsc, $plugin_config);

	if ($plugin_config['dev']['enable_outgoing']) {
		$ok = true;
	}

	return $ok;
}

function dev_hook_playsmsd()
{
	if (!core_playsmsd_timer(60)) {
		return;
	}

	$db_query = "SELECT smslog_id,uid FROM " . _DB_PREF_ . "_tblSMSOutgoing WHERE p_gateway='dev' AND p_status=0";
	$db_result = dba_query($db_query);
	while ($db_row = dba_fetch_array($db_result)) {
		if ($db_row['smslog_id'] && $db_row['uid']) {
			$p_status = 3;
			dlr($db_row['smslog_id'], $db_row['uid'], $p_status);
		}
	}
}
