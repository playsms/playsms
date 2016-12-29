<?php
defined('_SECURE_') or die('Forbidden');

$db_query = "SELECT * FROM " . _DB_PREF_ . "_gatewayOrange_config";
$db_result = dba_query($db_query);
if ($db_row = dba_fetch_array($db_result)) {
	$plugin_config['orange']['country_code'] = $db_row['cfg_country_code'];
	$plugin_config['orange']['client_id'] = $db_row['cfg_client_id'];
	$plugin_config['orange']['client_secret'] = $db_row['cfg_client_secret'];
	$plugin_config['orange']['sender_address'] = $db_row['cfg_sender_address'];
	$plugin_config['orange']['sender_name'] = $db_row['cfg_sender_name'];
	$plugin_config['orange']['send_url'] = $db_row['cfg_send_url'];
	$plugin_config['orange']['datetime_timezone'] = $db_row['cfg_datetime_timezone'];

	$plugin_config['orange']['token'] = $db_row['cfg_token'];
	$plugin_config['orange']['token_updated_at'] = $db_row['cfg_token_updated_at'];
	$plugin_config['orange']['token_expirate_at'] = $db_row['cfg_token_expirate_at'];

}

// smsc configuration
$plugin_config['orange']['_smsc_config_'] = array(
	'country_code' => _('Country Code'),
	'client_id' => _('client Id'),
	'client_secret' => _('client Secret'),
	'sender_address' => _('Sender Address'),
	'sender_name' => _('Sender Name'),
	'send_url' => _('Orange API URL'),
	'datetime_timezone' => _('Module Timezone')
);
