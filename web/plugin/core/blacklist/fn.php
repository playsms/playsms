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
 * Check if IP address deserved to get listed in blacklist, if deserved then blacklist_addip()
 *
 * @param string $ip
 *        single IP address
 * @return boolean TRUE on checked (not necessarily added)
 */
function blacklist_checkip($ip) {
	$ret = core_call_hook();
	return $ret;
}

/**
 * Reset IP address login attempt counter 
 *
 * @param string $ip
 *        single IP address
 * @return boolean TRUE on resetted counter
 */
function blacklist_clearip($ip) {
	$ret = core_call_hook();
	return $ret;
}

/**
 * Add IP address to blacklist
 *
 * @param string $ip
 *        single IP address
 * @return boolean TRUE on added
 */
function blacklist_addip($ip) {
	$ret = core_call_hook();
	return $ret;
}

/**
 * Remove IP address from blacklist
 *
 * @param string $ip
 *        single IP address
 * @return boolean TRUE on removed
 */
function blacklist_removeip($ip) {
	$ret = core_call_hook();
	return $ret;
}

/**
 * Get IP addresses from blacklist
 *
 * @return array IP addresses
 */
function blacklist_getips() {
	$ret = core_call_hook();
	return $ret;
}

/**
 * Check IP address is exists in blacklist
 *
 * @param string $ip
 *        single IP address
 * @return boolean TRUE when found and FALSE if not found
 */
function blacklist_ifipexists($ip) {
	$ret = core_call_hook();
	return $ret;
}
