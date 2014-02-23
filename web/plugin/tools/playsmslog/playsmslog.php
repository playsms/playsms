<?php
defined('_SECURE_') or die('Forbidden');

if(!auth_isadmin()){auth_block();};

switch ($op)
{
	case "playsmslog_list":
		unset($tpl);
		$tpl = array(
			'name' => 'playsmslog',
			'var' => array(
				'HTTP_PATH_THEMES' => _HTTP_PATH_THEMES_,
				'LOG_FILE' => $core_config['apps_path']['logs'] . '/playsms.log',
				'LOG' => playsmslog_view(),
				'View log' => _('View log')
			)
		);
		_p(tpl_apply($tpl));
		break;
}

?>
