<?php
defined('_SECURE_') or die('Forbidden');
if(!auth_isadmin()){auth_block();};

include $core_config['apps_path']['plug']."/gateway/nexmo/config.php";

$gw = core_gateway_get();

if ($gw == $plugin_config['nexmo']['name']) {
	$status_active = "<span class=status_active />";
} else {
	$status_active = "<span class=status_inactive />";
}

$callback_url = $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/plugin/gateway/nexmo/callback.php";
$callback_url = str_replace("//", "/", $callback_url);
$callback_url = "http://".$callback_url;

switch (_OP_) {
	case "manage":
		if ($err = $_SESSION['error_string']) {
			$error_content = "<div class=error_string>$err</div>";
		}
		$tpl = array(
		    'name' => 'nexmo',
		    'vars' => array(
			'ERROR' => $error_content,
			'Manage nexmo' => _('Manage nexmo'),
			'Gateway name' => _('Gateway name'),
			'Nexmo URL' => _('Nexmo URL'),
			'API key' => _('API key'),
			'API secret' => _('API secret'),
			'Module sender ID' => _('Module sender ID'),
			'Module timezone' => _('Module timezone'),
			'Save' => _('Save'),
			'Notes' => _('Notes'),
			'HINT_JSON_FORMAT' => _hint(_('Use JSON format URL')),
			'HINT_FILL_SECRET' => _hint(_('Fill to change the API secret')),
			'HINT_GLOBAL_SENDER' => _hint(_('Max. 16 numeric or 11 alphanumeric char. empty to disable')),
			'HINT_TIMEZONE' => _hint(_('Eg: +0700 for Jakarta/Bangkok timezone')),
			'CALLBACK_URL_IS' => _('Your callback URL is'),
			'CALLBACK_URL_ACCESSIBLE' => _('Your callback URL should be accessible from Nexmo'),
			'NEXMO_PUSH_DLR' => _('Nexmo will push DLR and incoming SMS to your callback URL'),
			'NEXMO_IS_BULK' => _('Nexmo is a bulk SMS provider'),
			'NEXMO_FREE_CREDIT' => _('free credits are available for testing purposes'),
			'BUTTON_BACK' => _back('index.php?app=main&inc=feature_gatewaymanager&op=gatewaymanager_list'),
			'status_active' => $status_active,
			'nexmo_param_url' => $plugin_config['nexmo']['url'],
			'nexmo_param_api_key' => $plugin_config['nexmo']['api_key'],
			'nexmo_param_global_sender' => $plugin_config['nexmo']['global_sender'],
			'nexmo_param_datetime_timezone' => $plugin_config['nexmo']['datetime_timezone'],
			'callback_url' => $callback_url
		    )
		);
		_p(tpl_apply($tpl));
		break;
	case "manage_save":
		$up_url = $_POST['up_url'];
		$up_api_key = $_POST['up_api_key'];
		$up_api_secret = $_POST['up_api_secret'];
		$up_global_sender = $_POST['up_global_sender'];
		$up_global_timezone = $_POST['up_global_timezone'];
		$_SESSION['error_string'] = _('No changes have been made');
		if ($up_url && $up_api_key) {
			if ($up_api_secret) {
				$api_secret_change = "cfg_api_secret='$up_api_secret',";
			}
			$db_query = "
				UPDATE "._DB_PREF_."_gatewayNexmo_config
				SET c_timestamp='".mktime()."',
				cfg_url='$up_url',
				cfg_api_key='$up_api_key',
				".$api_secret_change."
				cfg_global_sender='$up_global_sender',
				cfg_datetime_timezone='$up_global_timezone'";
			if (@dba_affected_rows($db_query)) {
				$_SESSION['error_string'] = _('Gateway module configurations has been saved');
			}
		}
		header("Location: "._u('index.php?app=main&inc=gateway_nexmo&op=manage'));
		exit();
		break;
}
