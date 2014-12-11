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
 * @return ip_address
 */
function firewall_getip($id) {
	$condition = array('id' => $id);
	$row = dba_search(_DB_PREF_ . '_featureFirewall', 'ip_address', $condition);
	$ret = $row[0]['ip_address'];
	return $ret;
}

/**
 * Check if IP address deserved to get listed in blacklist, if deserved then blacklist_addip()
 *
 * @param string $ip
 *        single IP address
 * @return boolean TRUE on checked (not necessarily added)
 */
function firewall_hook_blacklist_checkip($ip) {
	$hash = md5($ip);
	$data = registry_search(0, 'feature', 'firewall');
	$login_attempt = $data['feature']['firewall'][$hash];

	if($login_attempt > 10){
		blacklist_addip($ip);
	}

	$items[$hash] = $login_attempt ? $login_attempt + 1 : 1;
	registry_update(0, 'feature', 'firewall', $items);
	$ret = TRUE;
	return $ret;
}

/**
 * Add IP address to blacklist
 *
 * @param string $ip
 *        single IP address
 * @return boolean TRUE on added
 */
function firewall_hook_blacklist_addip($ip) {
	$ret = FALSE;
	$db_query = "
			INSERT INTO " . _DB_PREF_ . "_featureFirewall (ip_address)
			VALUES ('$ip')";
	if(!blacklist_ifipexists($ip)){
		$new_ip = @dba_insert_id($db_query);
		if($new_ip){
			$ret = TRUE;
		}
	}
	return $ret;
}

/**
 * Remove IP address from blacklist
 *
 * @param string $ip
 *        single IP address
 * @return boolean TRUE on removed
 */
function firewall_hook_blacklist_removeip($ip) {
	$ret = FALSE;
	$condition = array('ip_address' => $ip);
	$removed = dba_remove(_DB_PREF_.'_featureFirewall', $condition);
	if($removed){
		$ret = TRUE;
	}
	return $ret;
}

/**
 * Get IP addresses from blacklist
 *
 * @return array IP addresses
 */
function firewall_hook_blacklist_getips() {
	$ret = dba_search(_DB_PREF_.'_featureFirewall');
	return $ret;
}

/**
 * Check IP address is exists in blacklist
 *
 * @param string $ip
 *        single IP address
 * @return boolean TRUE when found and FALSE if not found
 */
function firewall_hook_blacklist_ifipexists($ip) {
	$ret = FALSE;
	$condition = array('ip_address' => $ip);
	$row = dba_search(_DB_PREF_.'_featureFirewall', 'ip_address', $condition);
	if($row){
		$ret = TRUE;
	}
	return $ret;
}
