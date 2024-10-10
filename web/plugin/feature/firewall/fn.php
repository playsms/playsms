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
	$row = dba_search(_DB_PREF_ . '_featureFirewall', 'ip_address', ['id' => $id]);

	return isset($row[0]['ip_address']) ? $row[0]['ip_address'] : '';
}

/**
 * Check if IP address deserved to get listed in blacklist, if deserved then blacklist_addip()
 *
 * @param string $ip single IP address
 * @return bool true on checked (not necessarily added)
 */
function firewall_hook_blacklist_checkip($ip)
{
	global $core_config, $plugin_config;

	$ip = trim($ip);

	if ($core_config['main']['brute_force_detection']) {
		$hash = md5($ip);
		$data = registry_search(0, 'feature', 'firewall');
		$login_attempt = $data['feature']['firewall'][$hash];

		if ($login_attempt >= $plugin_config['firewall']['login_attempt_limit']) {
			blacklist_addip($ip);
		}

		$items[$hash] = $login_attempt ? $login_attempt + 1 : 1;
		if (registry_update(0, 'feature', 'firewall', $items)) {

			return true;
		}
	}

	return false;
}

/**
 * Reset IP address login attempt counter
 *
 * @param string $ip single IP address
 * @return bool true on reset counter
 */
function firewall_hook_blacklist_clearip($ip)
{
	$ip = trim($ip);

	$hash = md5($ip);
	if (registry_remove(0, 'feature', 'firewall', $hash)) {

		return true;
	}

	return false;
}

/**
 * Add IP address to blacklist
 *
 * @param string $ip single IP address
 * @return bool true on added
 */
function firewall_hook_blacklist_addip($ip)
{
	$ip = trim($ip);

	if (!blacklist_ifipexists($ip)) {
		$db_query = "INSERT INTO " . _DB_PREF_ . "_featureFirewall (ip_address) VALUES (?)";
		if ($id = dba_insert_id($db_query, [$ip])) {
			_log('add IP to blacklist ip:' . $ip . ' id:' . $id, 2, 'firewall_hook_blacklist_addip');

			return true;
		}
	}

	return false;
}

/**
 * Remove IP address from blacklist
 *
 * @param string $ip single IP address
 * @return bool true on removed
 */
function firewall_hook_blacklist_removeip($ip)
{
	$ip = trim($ip);

	if (dba_remove(_DB_PREF_ . '_featureFirewall', ['ip_address' => $ip])) {
		_log('remove IP from blacklist ip:' . $ip, 2, 'firewall_hook_blacklist_removeip');

		return true;
	}

	return false;
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
 * @param string $ip single IP address
 * @return bool true when found and false if not found
 */
function firewall_hook_blacklist_ifipexists($ip)
{
	$ip = trim($ip);

	$list = firewall_hook_blacklist_getips();
	foreach ( $list as $db_row ) {
		$ip_or_net = $db_row['ip_address'];
		if (filter_var($ip_or_net, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
			if (core_net_match($ip_or_net, $ip, true)) {

				return true;
			}
		} else if ($ip_or_net == $ip) {

			return true;
		}
	}

	return false;
}
