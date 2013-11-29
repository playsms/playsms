<?php

function gatewaymanager_get_status($name) {
	global $core_config;
	if ($core_config['module']['gateway'] == $name) {
		$ret = TRUE;
	} else {
		$ret = FALSE;
	}
	return $ret;
}

function gatewaymanager_list() {
	global $core_config;
	$upload_path = $core_config['apps_path']['plug'] . '/gateway/';
	$dir = opendir($upload_path);
	$z = 0;
	while ($fn = readdir($dir)) {
		$template = preg_match('/^_/', $fn, $match);
		if (is_dir($upload_path . $fn) && $f != "." && $f != ".." && $template != true && $fn != 'common') {
			$subdir_tab[$z]['name'] .= $fn;
			$subdir_tab[$z]['version'] .= trim(file_get_contents($apps_path['plug'] . '/gateway/' . $f . '/docs/VERSION'));
			$subdir_tab[$z]['date'] .= date($core_config['datetime']['format'], filemtime($upload_path . $f));
			if (gatewaymanager_get_status($fn)) {
				$subdir_tab[$z][status] .= '<span class=status_enabled />';
			} else {
				$subdir_tab[$z][status] .= '<span class=status_disabled />';
			}
			$z++;
		}
	}
	return $subdir_tab;
}

function gatewaymanager_display() {
	global $core_config;
	$content = "
		<div class=table-responsive>
		<table class=playsms-table-list id='gatewaymanager_view'>
			<thead><tr>
				<th width=15%>" . _('Name') . "</th>
				<th width=25%>" . _('Description') . "</th>
				<th width=10%>" . _('Version') . "</th>
				<th width=20%>" . _('Author') . "</th>
				<th width=20%>" . _('Date') . "</th>
				<th width=10%>" . _('Status') . "</th>
				<th width=10%>" . _('Action') . "</th>
			</tr></thead>
			<tbody>";
	$subdir_tab = gatewaymanager_list();
	for ($l = 0; $l < sizeof($subdir_tab); $l++) {
		unset($gateway_info);
		$c_gateway = $subdir_tab[$l]['name'];
		$xml_file = $core_config['apps_path']['plug'] . '/gateway/' . $c_gateway . '/docs/info.xml';
		if ($fc = file_get_contents($xml_file)) {
			$gateway_info = core_xml_to_array($fc);
			$gateway_info['status'] = $subdir_tab[$l]['status'];
		}
		if ($gateway_info['name']) {
			$content .= "
				<tr>
					<td>" . $gateway_info['name'] . "</td>
					<td>" . $gateway_info['description'] . "</td>
					<td>" . $gateway_info['release'] . "</td>
					<td>" . $gateway_info['author'] . "</td>
					<td>" . $gateway_info['date'] . "</td>
					<td>" . $gateway_info['status'] . "</td>
					<td><a href='index.php?app=menu&inc=gateway_".$c_gateway."&op=manage'><span class='glyphicon glyphicon-wrench'></span></a></td>
				</tr>";
		}
	}
	$content .= "</tbody></table></div>";
	return $content;
}

?>