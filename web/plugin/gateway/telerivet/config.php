<?php
defined('_SECURE_') or die('Forbidden');

$db_query = "SELECT * FROM " . _DB_PREF_ . "_gatewayTelerivet_config";
$db_result = dba_query($db_query);
if ($db_row = dba_fetch_array($db_result)) {
	$plugin_config['telerivet']['name'] = $db_row['cfg_name'];
	$plugin_config['telerivet']['url'] = $db_row['cfg_url'];
	$plugin_config['telerivet']['api_key'] = $db_row['cfg_api_key'];
	$plugin_config['telerivet']['project_id'] = $db_row['cfg_project_id'];
	$plugin_config['telerivet']['status_url'] = $db_row['cfg_status_url'];
	$plugin_config['telerivet']['status_secret'] = $db_row['cfg_status_secret'];
}

// smsc configuration
$plugin_config['telerivet']['_smsc_config_'] = array();

