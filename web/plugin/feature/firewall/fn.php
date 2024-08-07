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
 * Get ip_address by id
 *
 * @param string $id
 * @return string ip_address
 */
function firewall_getip($id)
{
	$condition = [
		'id' => $id
	];
	$row = dba_search(_DB_PREF_ . '_featureFirewall', 'ip_address', $condition);
	$ret = $row[0]['ip_address'];

	return $ret;
}

/**
 * Check if IP address deserved to get listed in blacklist, if deserved then blacklist_addip()
 *
 * @param string $label single label, can be $username or $uid, its up to the implementator
 * @param string $ip single IP address
 * @return bool true on checked (not necessarily added)
 */
function firewall_hook_blacklist_checkip($label, $ip)
{
	global $core_config, $plugin_config;

	$ret = false;

	$label = trim($label);
	$ip = trim($ip);

	if ($core_config['main']['brute_force_detection']) {
		$hash = md5($label . $ip);
		$data = registry_search(0, 'feature', 'firewall');
		$login_attempt = $data['feature']['firewall'][$hash];

		if ($login_attempt >= $plugin_config['firewall']['login_attempt_limit']) {
			blacklist_addip($label, $ip);
		}

		$items[$hash] = $login_attempt ? $login_attempt + 1 : 1;
		if (registry_update(0, 'feature', 'firewall', $items)) {
			$ret = true;
		}
	}

	return $ret;
}

/**
 * Reset IP address login attempt counter
 *
 * @param string $label single label, can be $username or $uid, its up to the implementator
 * @param string $ip single IP address
 * @return bool true on reset counter
 */
function firewall_hook_blacklist_clearip($label, $ip)
{
	$ret = false;

	$label = trim($label);
	$ip = trim($ip);

	$hash = md5($label . $ip);
	if (registry_remove(0, 'feature', 'firewall', $hash)) {
		$ret = true;
	}

	return $ret;
}

/**
 * Add IP address to blacklist
 *
 * @param string $label single label, can be $username or $uid, its up to the implementator
 * @param string $ip single IP address
 * @return bool true on added
 */
function firewall_hook_blacklist_addip($label, $ip)
{
	$ret = false;

	$label = trim($label);
	$ip = trim($ip);

	if (!blacklist_ifipexists($label, $ip)) {
		$uid = user_username2uid($label);
		$db_query = "INSERT INTO " . _DB_PREF_ . "_featureFirewall (uid,ip_address) VALUES (?,?)";
		if ($id = dba_insert_id($db_query, [$uid, $ip])) {
			_log('add IP to blacklist ip:' . $ip . ' uid:' . $uid . ' id:' . $id, 2, 'firewall_hook_blacklist_addip');
			$ret = true;
		}
	}

	return $ret;
}

/**
 * Remove IP address from blacklist
 *
 * @param string $label single label, can be $username or $uid, its up to the implementator
 * @param string $ip single IP address
 * @return bool true on removed
 */
function firewall_hook_blacklist_removeip($label, $ip)
{
	$ret = false;

	$label = trim($label);
	$ip = trim($ip);

	$c_uid = user_username2uid($label);
	$condition = array(
		'uid' => $c_uid,
		'ip_address' => $ip
	);
	if (dba_remove(_DB_PREF_ . '_featureFirewall', $condition)) {
		_log('remove IP from blacklist ip:' . $ip . ' uid:' . $c_uid, 2, 'firewall_hook_blacklist_removeip');
		$ret = true;
	}

	return $ret;
}

/**
 * Get IP addresses from blacklist
 *
 * @return array IP addresses
 */
function firewall_hook_blacklist_getips()
{
	$ret = dba_search(_DB_PREF_ . '_featureFirewall');

	return $ret;
}

/**
 * Check IP address is exists in blacklist
 *
 * @param string $label single label, can be $username or $uid, its up to the implementator
 * @param string $ip single IP address
 * @return bool true when found and false if not found
 */
function firewall_hook_blacklist_ifipexists($label, $ip)
{
	$label = trim($label);
	$ip = trim($ip);

	$uid = user_username2uid($label);

	$db_query = "SELECT ip_address FROM " . _DB_PREF_ . "_featureFirewall WHERE uid=?";
	$db_result = dba_query($db_query, [$uid]);
	while ($db_row = dba_fetch_array($db_result)) {
		$ip_or_net = $db_row['ip_address'];
		if (preg_match('/[\/]/', $ip_or_net)) {
			if (core_net_match($ip_or_net, $ip, true)) {

				return true;
			}
		} else if ($ip_or_net == $ip) {

			return true;
		}
	}

	return false;
}
