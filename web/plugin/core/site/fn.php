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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with playSMS. If not, see <http://www.gnu.org/licenses/>.
 */
defined('_SECURE_') or die('Forbidden');

/**
 * Get site configuration
 *
 * @param integer $uid
 *        User ID
 * @return array Site configuration
 */
function site_config_get($uid = 0) {
	global $user_config, $plugin_config;
	
	$c_uid = ((int) $uid ? (int) $uid : $user_config['uid']);
	
	$reg = registry_search($c_uid, 'core', 'site_config');
	$plugin_config['site']['site_config'] = $reg['core']['site_config'];
	
	return $plugin_config['site']['site_config'];
}

/**
 * Set option to site configuration
 *
 * @param array $config
 *        Partial or full site configuration
 * @return array Site configuration
 */
function site_config_set($config) {
	global $user_config, $plugin_config;
	
	registry_remove($user_config['uid'], 'core', 'site_config');
	
	// save domain owner
	if (($user_config['status'] == 2) || ($user_config['status'] == 3)) {
		$items['uid'] = $user_config['uid'];
	} else {
		$items['uid'] = 0;
	}
	
	registry_update($user_config['uid'], 'core', 'site_config', $config);
	
	return site_config_get();
}

/**
 * Get site configuration by domain name
 *
 * @param string $domain
 *        Domain name, hostname or IP address
 * @return array Site configuration matched the domain
 */
function site_config_getbydomain($domain) {
	$list = array();
	
	if ($domain) {
		$list = registry_search_record(array(
			'registry_group' => 'core',
			'registry_family' => 'site_config',
			'registry_key' => 'domain',
			'registry_value' => $domain 
		));
	}
	
	return $list;
}

// ----- HOOKS -----


/**
 * Hook to core_themes_get()
 *
 * @return string Themes name or empty
 */
function site_hook_core_themes_get() {
	$ret = '';
	
	$site_config = site_config_get();
	
	if (strtolower(trim($_SERVER['HTTP_HOST'])) == strtolower(trim($site_config['domain']))) {
		if ($site_config['themes_module']) {
			$ret = $site_config['themes_module'];
		}
	}
	
	return $ret;
}
