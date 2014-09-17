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

function pluginmanager_get_status($plugin_category, $name) {
	if ($plugin_category == "themes") {
		if (core_themes_get() == $name) {
			$ret = TRUE;
		} else {
			$ret = FALSE;
		}
	} else if ($plugin_category == "language") {
		if (core_lang_get() == $name) {
			$ret = TRUE;
		} else {
			$ret = FALSE;
		}
	} else {
		$ret = TRUE;
	}
	return $ret;
}

function pluginmanager_list($plugin_category) {
	global $core_config;
	$upload_path = $core_config['apps_path']['plug'] . "/" . $plugin_category . "/";
	$dir = opendir($upload_path);
	$z = 0;
	while ($fn = readdir($dir)) {
		$template = preg_match('/^_/', $fn, $match);
		if (is_dir($upload_path . $fn) && $f != "." && $f != ".." && $template != true && $fn != 'common') {
			$subdir_tab[$z]['name'] .= $fn;
			$subdir_tab[$z]['version'] .= trim(file_get_contents($core_config['apps_path']['plug'] . "/" . $plugin_category . "/" . $f . "/docs/VERSION"));
			$subdir_tab[$z]['date'] .= date($core_config['datetime']['format'], filemtime($upload_path . $f));
			if (pluginmanager_get_status($plugin_category, $fn)) {
				$subdir_tab[$z][status] .= '<span class=status_enabled />';
			} else {
				$subdir_tab[$z][status] .= '<span class=status_disabled />';
			}
			$z++;
		}
	}
	return $subdir_tab;
}

function pluginmanager_display($plugin_category) {
	global $core_config;
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
	$subdir_tab = pluginmanager_list($plugin_category);
	for($l = 0; $l < sizeof($subdir_tab); $l++) {
		unset($plugin_info);
		$xml_file = $core_config['apps_path']['plug'] . "/" . $plugin_category . "/" . $subdir_tab[$l]['name'] . "/docs/info.xml";
		if ($fc = file_get_contents($xml_file)) {
			$plugin_info = core_xml_to_array($fc);
			$plugin_info['status'] = $subdir_tab[$l]['status'];
		}
		if ($plugin_info['name']) {
			$content .= "
				<tr>
					<td>" . $plugin_info['name'] . "</td>
					<td>" . $plugin_info['description'] . "</td>
					<td>" . $plugin_info['release'] . "</td>
					<td>" . $plugin_info['author'] . "</td>
					<td>" . $plugin_info['date'] . "</td>
					<td>" . $plugin_info['status'] . "</td>
				</tr>";
		}
	}
	$content .= "</tbody></table></div>";
	return $content;
}
