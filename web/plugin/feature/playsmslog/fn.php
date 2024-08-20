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
 * View content of log file
 * 
 * @param int $nline show this number of line
 * @return string
 */
function playsmslog_view($nline = 1000)
{
	global $core_config;

	$content = '';

	$nline = (int) $nline > 1000 ? 1000 : (int) $nline;
	$nline = (int) $nline < 10 ? 10 : (int) $nline;

	$log_file = $core_config['apps_path']['logs'] . '/playsms.log';

	if (is_file($log_file)) {
		$content = shell_exec('tail -n ' . $nline . ' ' . $log_file);
	}

	return $content;
}
