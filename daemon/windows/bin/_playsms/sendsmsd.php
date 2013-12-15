#!/usr/bin/php -q
<?php

set_time_limit(600);

//error_reporting(0);
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));

$continue = false;
if (($PLAYSMS_PATH = $argv[1]) && (file_exists($PLAYSMS_PATH))) {
	$DAEMON_PROCESS = true;
	chdir($PLAYSMS_PATH);
	if (file_exists('init.php')) {
		include 'init.php';
		$fn = $apps_path['libs'].'/function.php';
		if ($core_config['daemon_process'] && file_exists($fn) && $core_config['issendsmsd']) {
			include $fn;
			if ($apps_path['incs']) {
				$continue = true;
			}
		}
	}
}

if ($continue) {
	if (trim($argv[2]) == '_GETQUEUE_') {
		$queue = '';
		if ((int) $core_config['sendsmsd_queue'] > 0) {
			$extas = array('LIMIT' => (int) $core_config['sendsmsd_queue']);
		}
		$list = dba_search(_DB_PREF_.'_tblSMSOutgoing_queue', 'queue_code', array('flag' => '0'), '', $extras);
		foreach ($list as $db_row) {
			$queue .= 'Q_'.$db_row['queue_code'].' ';
		}
		if ($queue = trim($queue)) {
			echo $queue;
		}
		exit();
	}
	if ((trim($argv[2]) == '_PROCESS_') && trim($argv[3])){
		$c_queue = explode('_', trim($argv[3]));
		if ($c_queue[0]=='Q' && trim($c_queue[1])) {
			sendsmsd(trim($c_queue[1]), $core_config['sendsmsd_limit']);
		}
	}
}

