<?php
defined('_SECURE_') or die('Forbidden');
if (!auth_isadmin()) {
	auth_block();
};

include $core_config['apps_path']['plug'] . "/gateway/gammu/config.php";

switch (_OP_) {
	case "manage":
		if ($err = TRUE) {
			$content = _dialog();
		}
		$content.= "
			<h2>" . _('Manage gammu') . "</h2>
			<table class=playsms-table>
				<tbody>
				<tr>
					<td class=label-sizer>" . _('Gateway name') . "</td><td>gammu</td>
				</tr>
				</tbody>
			</table>";
		$content.= _back('index.php?app=main&inc=core_gateway&op=gateway_list');
		_p($content);
		break;
}
