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

/**
 * intercept sendsms and replace certain word templates in $sms_msg
 * 
 * @param $sms_sender sender number
 * @param $sms_footer sender signiture/footer
 * @param $sms_to destination number
 * @param $sms_msg SMS message
 * @param $uid User ID
 * @param $gpid Group phonebook ID
 * @param $sms_type Type of SMS
 * @param $unicode Whether or not a unicode message
 * @param $smsc Gateway
 * @return array modified parameters
 *     [
 *         param => sms_msg,	// modified SMS message
 *     ]
 */
function msgtemplate_hook_sendsms_process_before($sms_sender, $sms_footer, $sms_to, $sms_msg, $uid, $gpid, $sms_type, $unicode, $queue_code, $smsc)
{
	// parameters modified
	$ret['modified'] = true;

	// the modification to $sms_msg, case insensitive
	$text = $sms_msg;
	$text = str_ireplace('#NAME#', phonebook_number2name($uid, $sms_to), $text);
	$text = str_ireplace('#NUM#', $sms_to, $text);
	$ret['param']['sms_msg'] = $text;

	// log it
	_log("to:" . $sms_to . " msg:" . $sms_msg . " replacedby:" . $ret['param']['sms_msg'], 3, "msgtemplate");

	return $ret;
}

function msgtemplate_hook_sendsms_get_template()
{
	global $user_config;

	$ret = [];

	$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureMsgtemplate WHERE uid=? ORDER BY t_title ASC";
	$db_result = dba_query($db_query, [$user_config['uid']]);
	$i = 0;
	while ($db_row = dba_fetch_array($db_result)) {
		$ret[$i]['text'] = $db_row['t_text'];
		$ret[$i]['title'] = $db_row['t_title'];
		$i++;
	}

	return $ret;
}
