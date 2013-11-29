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
				$subdir_tab[$z][status] .= '<span class=status_enabled></span>';
			} else {
				$subdir_tab[$z][status] .= '<span class=status_disabled></span>';
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
				<th width=30%>" . _('Name') . "</th>
				<th width=50%>" . _('Description') . "</th>
				<th width=10%>" . _('Version') . "</th>
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
			$gw_list[$gateway_info['name']] = array(
				'link' => "index.php?app=menu&inc=gateway_".$c_gateway."&op=manage",
				'name' => $gateway_info['name'],
				'description' => $gateway_info['description'],
				'release' => $gateway_info['release'],
				'status' => $gateway_info['status']
			);
		}
	}
	ksort($gw_list);
	foreach ($gw_list as $gw) {
		$content .= "
			<tr>
				<td>" . $gw['name'] . "</td>
				<td>" . $gw['description'] . "</td>
				<td>" . $gw['release'] . "</td>
				<td>
					<a href='".$gw['link']."'><span class='glyphicon glyphicon-wrench'></span></a>&nbsp;
					".$gw['status']."&nbsp;
				</td>
			</tr>";
		
	}
	$content .= "</tbody></table></div>";
	return $content;
}

?>