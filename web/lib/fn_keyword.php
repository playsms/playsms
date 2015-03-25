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
 * Check available keyword or keyword that hasn't been added
 * This function is equivalent to checkavailablekeyword()
 *
 * @param $keyword keyword
 * @return TRUE if available, FALSE if already exists or not available
 */
function keyword_isavail($keyword) {
	global $reserved_keywords, $core_config;
	
	$ok = true;
	$reserved = false;
	
	$keyword = trim(strtoupper($keyword));
	for ($i = 0; $i < count($reserved_keywords); $i++) {
		if ($keyword == trim(strtoupper($reserved_keywords[$i]))) {
			$reserved = true;
		}
	}
	
	// if reserved returns not available, FALSE
	if ($reserved) {
		$ok = false;
	} else {
		for ($c = 0; $c < count($core_config['featurelist']); $c++) {
			
			// checkavailablekeyword() on hooks will return TRUE as well if keyword is available
			// so we're looking for FALSE value
			if (core_hook($core_config['featurelist'][$c], 'checkavailablekeyword', array(
				$keyword 
			)) === FALSE) {
				$ok = false;
				break;
			}
		}
	}
	return $ok;
}

/**
 * Opposite of keyword_isavail()
 *
 * @param string $keyword
 * @return boolean
 */
function keyword_isexists($keyword) {
	return !keyword_isavail($keyword);
}
