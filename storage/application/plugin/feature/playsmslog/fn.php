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

function playsmslog_view($nline = 1000) {
	global $core_config;

	$content = '';

	$nline = (int) $nline;
	$fn_log = $core_config['apps_path']['logs'] . '/' . $core_config['logfile'];
	
	if ($nline > 0 && file_exists($fn_log)) {
		$content = @shell_exec('tail -n ' . $nline . ' ' . $fn_log);
		
		$content = ( trim($content) ? core_display_text($content) : '' );
	}
	
	return $content;
}
