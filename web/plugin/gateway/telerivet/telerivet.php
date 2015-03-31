<?php
defined('_SECURE_') or die('Forbidden');
if (!auth_isadmin()) {
	auth_block();
}

include $core_config['apps_path']['plug'] . "/gateway/telerivet/config.php";

$gw = core_gateway_get();

if ($gw == $plugin_config['telerivet']['name']) {
	$status_active = "<span class=status_active />";
} else {
	$status_active = "<span class=status_inactive />";
}

switch (_OP_) {
	case "manage":
		if ($err = TRUE) {
			$content = _dialog();
		}
		$tpl = array(
			'name' => 'telerivet',
			'vars' => array(
				'DIALOG_DISPLAY' => $error_content,
				'Manage telerivet' => _('Manage telerivet'),
				'Gateway name' => _('Gateway name'),
				'Project ID' => _('Project ID'),
				'Telerivet URL' => _('Telerivet URL'),
				'API key' => _('API key'),
				'Callback URL' => _('Callback URL'),
				'Callback Secret' => _('Callback Secret'),
				'Save' => _('Save'),
				'HINT_FILL_KEY' => _hint(_('Fill to change the API key')),
				'BUTTON_BACK' => _back('index.php?app=main&inc=core_gateway&op=gateway_list'),
				'status_active' => $status_active,
				'telerivet_param_url' => $plugin_config['telerivet']['url'],
				'telerivet_param_project_id' => $plugin_config['telerivet']['project_id'],
				'telerivet_param_api_key' => $plugin_config['telerivet']['api_key'],
				'telerivet_param_status_url' => $plugin_config['telerivet']['status_url'],
				'telerivet_param_status_secret' => $plugin_config['telerivet']['status_secret'] 
			) 
		);
		_p(tpl_apply($tpl));
		break;
	case "manage_save":
		$up_url = $_POST['up_url'];
		$up_project_id = $_POST['up_project_id'];
		$up_api_key = $_POST['up_api_key'];
		$up_status_url = $_POST['up_status_url'];
		$up_status_secret = $_POST['up_status_secret'];
		$_SESSION['dialog']['info'][] = _('No changes has been made');
		if ($up_url && $up_project_id) {
			if ($up_api_key) {
				$api_key_change = "cfg_api_key='$up_api_key',";
			}
			$db_query = "
                UPDATE " . _DB_PREF_ . "_gatewayTelerivet_config
                SET c_timestamp='" . mktime() . "',
                cfg_url='$up_url',
                " . $api_key_change . "
                cfg_project_id='$up_project_id',
                cfg_status_url='$up_status_url',
                cfg_status_secret='$up_status_secret'";
			_log('query:' . $db_query, 2, 'config telerivet');
			if (@dba_affected_rows($db_query)) {
				$_SESSION['dialog']['info'][] = _('Gateway module configurations has been saved');
			}
		}
		header("Location: " . _u('index.php?app=main&inc=gateway_telerivet&op=manage'));
		exit();
		break;
}
