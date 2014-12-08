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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with playSMS.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('_SECURE_') or die('Forbidden');

function logger_print($log, $level='', $label='') {
	global $core_config, $user_config;

	$remote = ( trim($_SERVER['REMOTE_ADDR']) ? trim($_SERVER['REMOTE_ADDR']) : '-' );
	$host = ( trim($_SERVER['HTTP_HOST']) ? trim($_SERVER['HTTP_HOST']) : '-' );
	$logfile = ( $core_config['logfile'] ? $core_config['logfile'] : 'playsms.log' );

	// max log length is 1000
	if (strlen($log) > 1000) {
		$log = substr($log, 0, 1000);
	}

	// default level is 2
	$level = ( (int)$level > 0 ? (int)$level : 2 );

	// label should not have spaces, replace single space with double _
	$label = str_replace(' ', '__', $label);
	$label = ( $label ? $label : '-' );

	$username = ( $user_config['username'] ? $user_config['username'] : '-' );
	if (logger_get_level() >= $level) {
		$type = 'L'.$level;
		$fn = $core_config['apps_path']['logs'].'/'.$logfile;
		if ($fd = fopen($fn, 'a+')) {
			$dt = date($core_config['datetime']['format'], mktime());

			// REMOTE_ADDR HTTP_HOST DATE TIME PID USERNAME TYPE LABEL # LOG
			$message = stripslashes($remote." ".$host." ".$dt." "._PID_." ".$username." ".$type." ".$label." # ".$log);
			$message = str_replace("\n", " ", $message);
			$message = str_replace("\r", " ", $message);
			$message .= "\n";

			fputs($fd, $message);
			fclose($fd);
		}
	}
}

function logger_get_level() {
	global $core_config;
	return $core_config['logstate'];
}

function logger_set_level($level=0) {
	global $core_config;
	$core_config['logstate'] = $level;
}

function logger_audit() {
	global $core_config, $user_config;

	$host = ( trim($_SERVER['HTTP_HOST']) ? trim($_SERVER['HTTP_HOST']) : '-' );
	if ($core_config['logaudit']) {
		foreach ($_GET as $key => $val) {
			if(stristr($key, 'password') === FALSE) {
				$log .= $key.':'.$val.' ';
			} else {
				$log .= $key.':xxxxxx ';
			}
		}
		foreach ($_POST as $key => $val) {
			if(stristr($key, 'password') === FALSE) {
				$log .= $key.':'.$val.' ';
			} else {
				$log .= $key.':xxxxxx ';
			}
		}
		$log = trim($log);
		$logauditfile = ( $core_config['logauditfile'] ? $core_config['logauditfile'] : 'audit.log' );
		$username = ( $user_config['username'] ? $user_config['username'] : '-' );
		$ip = $_SERVER['REMOTE_ADDR'];
		$fn = $core_config['apps_path']['logs'].'/'.$logauditfile;
		if ($fd = fopen($fn, 'a+')) {
			$dt = date($core_config['datetime']['format'], mktime());
			$message = stripslashes($host." ".$dt." "._PID_." ".$username." ip:".$ip." ".$log);
			$message = str_replace("\n", " ", $message);
			$message = str_replace("\r", " ", $message);
			$message .= "\n";
			fputs($fd, $message);
			fclose($fd);
		}
	}
}
