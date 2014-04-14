<?php
defined('_SECURE_') or die('Forbidden');
if(!auth_isadmin()){auth_block();};

include $core_config['apps_path']['plug']."/gateway/smstools/config.php";

$gw = core_gateway_get();

if ($gw == $plugin_config['smstools']['name']) {
	$status_active = "<span class=status_active />";
} else {
	$status_active = "<span class=status_inactive />";
}

switch (_OP_) {
	case "manage":
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
			<h2>"._('Manage smstools')."</h2>
			<table class=playsms-table>
				<tbody>
				<tr>
					<td class=label-sizer>"._('Gateway name')."</td><td>smstools $status_active</td>
				</tr>
				</tbody>
			</table>";
		$content .= _back('index.php?app=main&inc=feature_gatewaymanager&op=gatewaymanager_list');
		_p($content);
		break;
}
