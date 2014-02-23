<?php
defined('_SECURE_') or die('Forbidden');
if(!auth_isadmin()){auth_block();};

include $apps_path['plug']."/gateway/nexmo/config.php";

$gw = core_gateway_get();

if ($gw == $nexmo_param['name']) {
	$status_active = "<span class=status_active />";
} else {
	$status_active = "<span class=status_inactive />";
}

$callback_url = $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/plugin/gateway/nexmo/callback.php";
$callback_url = str_replace("//", "/", $callback_url);
$callback_url = "http://".$callback_url;

switch ($op) {
	case "manage":
		if ($err = $_SESSION['error_string']) {
			$error_content = "<div class=error_string>$err</div>";
		}
		$tpl = array(
		    'name' => 'nexmo',
		    'var' => array(
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
			'BUTTON_BACK' => _back('index.php?app=menu&inc=tools_gatewaymanager&op=gatewaymanager_list'),
			'status_active' => $status_active,
			'nexmo_param_url' => $nexmo_param['url'],
			'nexmo_param_api_key' => $nexmo_param['api_key'],
			'nexmo_param_global_sender' => $nexmo_param['global_sender'],
			'nexmo_param_datetime_timezone' => $nexmo_param['datetime_timezone'],
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
		$_SESSION['error_string'] = _('No changes has been made');
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
		header("Location: index.php?app=menu&inc=gateway_nexmo&op=manage");
		exit();
		break;
	case "manage_activate":
		$db_query = "UPDATE "._DB_PREF_."_tblConfig_main SET c_timestamp='".mktime()."',cfg_gateway_module='nexmo'";
		$db_result = dba_query($db_query);
		$_SESSION['error_string'] = _('Gateway has been activated');
		header("Location: index.php?app=menu&inc=gateway_nexmo&op=manage");
		exit();
		break;
}
