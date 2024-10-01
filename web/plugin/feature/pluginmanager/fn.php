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

function _pluginmanager_get_status($plugin_category, $name)
{
	$ret = false;

	if ($name[0] != '.') {
		if ($plugin_category == "themes") {
			$ret = core_themes_get() == $name;
		} else if ($plugin_category == "language") {
			$ret = core_lang_get() == $name;
		} else {
			$ret = true;
		}
	}

	return $ret;
}

function _pluginmanager_list($plugin_category)
{
	global $core_config;

	$list = [];

	$upload_path = $core_config['apps_path']['plug'] . "/" . $plugin_category . "/";
	if (!is_dir($upload_path)) {

		return $list;
	}

	$plugins = [];
	$dir = opendir($upload_path);
	while ($plugin = readdir($dir)) {
		if (!(preg_match('/^_/', $plugin, $match) || $plugin == "." || $plugin == "..")) {
			$plugins[] = $plugin;
		}
	}
	sort($plugins);
	$i = 0;
	foreach ( $plugins as $plugin ) {
		if (is_dir($upload_path . $plugin) && $plugin != 'common') {
			$list[$i]['name'] = $plugin;
			$list[$i]['version'] = trim(file_get_contents($core_config['apps_path']['plug'] . "/" . $plugin_category . "/" . $plugin . "/docs/VERSION"));
			$list[$i]['date'] = date($core_config['datetime']['format'], filemtime($upload_path . $plugin));
			if (_pluginmanager_get_status($plugin_category, $plugin)) {
				$list[$i]['status'] .= '<span class=status_enabled />';
			} else {
				$list[$i]['status'] .= '<span class=status_disabled />';
			}
			$i++;
		}
	}

	return $list;
}

function pluginmanager_display($plugin_category)
{
	global $core_config;

	$plugin_category = core_sanitize_filename($plugin_category);

	$content = "
		<div class=table-responsive>
		<table class=playsms-table-list id='pluginmanager_view'>
			<thead><tr>
				<th width=15%>" . _('Name') . "</th>
				<th width=25%>" . _('Description') . "</th>
				<th width=10%>" . _('Version') . "</th>
				<th width=20%>" . _('Author') . "</th>
				<th width=20%>" . _('Date') . "</th>
				<th width=10%>" . _('Status') . "</th>
			</tr></thead>
			<tbody>";
	$plugins = [];
	$list = _pluginmanager_list($plugin_category);
	$c_count = sizeof($list);
	for ($l = 0; $l < $c_count; $l++) {
		unset($plugin);
		$xml_file = $core_config['apps_path']['plug'] . "/" . $plugin_category . "/" . $list[$l]['name'] . "/docs/info.xml";
		if (is_file($xml_file)) {
			if ($fc = file_get_contents($xml_file)) {
				$plugin = core_xml_to_array($fc);
				$plugin['status'] = $list[$l]['status'];
			}
			if ($plugin['name']) {
				$plugins[] = $plugin;
			}
		}
	}
	foreach ( $plugins as $plugin ) {
		$content .= "
			<tr>
				<td>" . $plugin['name'] . "</td>
				<td>" . $plugin['description'] . "</td>
				<td>" . $plugin['release'] . "</td>
				<td>" . $plugin['author'] . "</td>
				<td>" . $plugin['date'] . "</td>
				<td>" . $plugin['status'] . "</td>
			</tr>";

	}
	$content .= "</tbody></table></div>";

	return $content;
}
