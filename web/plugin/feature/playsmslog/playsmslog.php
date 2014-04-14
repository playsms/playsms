<?php
defined('_SECURE_') or die('Forbidden');

if (!auth_isadmin()) {
	auth_block();
};

switch (_OP_) {
	case "playsmslog_list":
		
		// get playsmsd status
		$json = shell_exec("playsmsd check_json");
		$playsmsd = json_decode($json);
		if ($playsmsd->IS_RUNNING) {
			$playsmsd_is_running = '<span class=status_enabled title="' . _('playSMS daemon is running') . '"></span>';
		} else {
			$playsmsd_is_running = '<span class=status_disabled title="' . _('playSMS daemon is NOT running') . '"></span>';
		}
		
		$tpl = array(
			'name' => 'playsmslog',
			'var' => array(
				'HTTP_PATH_THEMES' => _HTTP_PATH_THEMES_,
				'LOG_FILE' => $core_config['apps_path']['logs'] . '/playsms.log',
				'PLAYSMSD_IS_RUNNING' => $playsmsd_is_running,
				'LOG' => playsmslog_view() ,
				'Daemon status' => _('playSMS daemon status') ,
				'View log' => _('View log') ,
			)
		);
		_p(tpl_apply($tpl));
		break;
}
