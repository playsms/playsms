<?php
defined('_SECURE_') or die('Forbidden');

if (!auth_isadmin()) {
	auth_block();
};

switch (_OP_) {
	case "playsmslog_list":
	case "playsmslog_log":
		
		// get playsmsd status
		$json = shell_exec($plugin_config['playsmsd']['bin'].' check_json');
		$playsmsd = json_decode($json);
		if ($playsmsd->IS_RUNNING) {
			$playsmsd_is_running = '<span class=status_enabled title="' . _('playSMS daemon is running') . '"></span>';
		} else {
			$playsmsd_is_running = '<span class=status_disabled title="' . _('playSMS daemon is NOT running') . '"></span>';
		}
		
		$tpl = array(
			'name' => 'playsmslog',
			'vars' => array(
				'HTTP_PATH_THEMES' => _HTTP_PATH_THEMES_,
				'LOG_FILE' => $core_config['apps_path']['logs'] . '/playsms.log',
				'REFRESH_BUTTON' => _button('#', _('Refresh'), '', 'playsmslog_refresh'),
				'REFRESH_URL' => _u('index.php?app=main&inc=feature_playsmslog&op=playsmslog_log'),
				'PLAYSMSD_IS_RUNNING' => $playsmsd_is_running,
				'LOG' => playsmslog_view() ,
				'Daemon status' => _('playSMS daemon status') ,
				'View log' => _('View log') ,
			)
		);

		$content = tpl_apply($tpl);
		if (_OP_ == 'playsmslog_log') {
			ob_clean();
			_p(playsmslog_view());
			exit();
		} else {
			_p($content);
		}
		break;
}
