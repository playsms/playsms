<?php
defined('_SECURE_') or die('Forbidden');
if (!auth_isadmin()) { auth_block(); };

include $core_config['apps_path']['plug'] . "/gateway/kannel/config.php";

$gw = core_gateway_get();

if ($gw == $plugin_config['kannel']['name']) {
	$status_active = "<span class=status_active />";
} else {
	$status_active = "<span class=status_inactive />";
}

switch (_OP_) {
	case "manage":
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		// Handle DLR options config (emmanuel)
		/* DLR Kannel value
		   1: Delivered to phone
		   2: Non-Delivered to Phone
		   4: Queued on SMSC
		   8: Delivered to SMSC
		   16: Non-Delivered to SMSC
		*/
		$up_dlr_box = "<input type='checkbox' name='dlr_box[]' value='1' ".$checked[0]."> "._('Delivered to phone')."<br />";
		$up_dlr_box .= "<input type='checkbox' name='dlr_box[]' value='2' ".$checked[1]."> "._('Non-Delivered to phone')."<br />";
		$up_dlr_box .= "<input type='checkbox' name='dlr_box[]' value='4' ".$checked[2]."> "._('Queued on SMSC')."<br />";
		$up_dlr_box .= "<input type='checkbox' name='dlr_box[]' value='8' ".$checked[3]."> "._('Delivered to SMSC')."<br />";
		$up_dlr_box .= "<input type='checkbox' name='dlr_box[]' value='16' ".$checked[4]."> "._('Non-Delivered to SMSC')."<br />";
		// end of Handle DLR options config (emmanuel)
		if ($plugin_config['kannel']['local_time']) {
			$selected1 = 'selected';
		} else {
			$selected2 = 'selected';
		}
		$option_local_time = "
			<option value=1 $selected1>"._('Yes')."</option>
			<option value=0 $selected2>"._('No')."</option>
			";
		$admin_port = $plugin_config['kannel']['admin_port'];
		$admin_host = $plugin_config['kannel']['sendsms_host'];
		$admin_host = ( $admin_port ? $admin_host.':'.$admin_port : $admin_host );
		$admin_password = $plugin_config['kannel']['admin_password'];
		$url = 'http://'.$admin_host.'/status?password='.urlencode($admin_password);
		$kannel_status = file_get_contents($url);

		$content .= "
			<h2>" . _('Manage kannel') . "</h2>
			<form action=index.php?app=main&inc=gateway_kannel&op=manage_save method=post>
			"._CSRF_FORM_."
			<table class=playsms-table cellpadding=1 cellspacing=2 border=0>
				<tbody>
				<tr>
					<td class=label-sizer>" . _('Gateway name') . "</td><td>kannel $status_active</td>
				</tr>
				<tr>
					<td>" . _('Username') . "</td><td><input type=text size=30 maxlength=30 name=up_username value=\"" . $plugin_config['kannel']['username'] . "\"></td>
				</tr>
				<tr>
					<td>" . _('Password') . "</td><td><input type=password size=30 maxlength=30 name=up_password value=\"\"> "._hint(_('Fill to change the password'))."</td>
				</tr>
				<tr>
					<td>"._('Module sender ID')."</td><td><input type=text size=30 maxlength=16 name=up_global_sender value=\"".$plugin_config['kannel']['global_sender']."\"> "._hint(_('Max. 16 numeric or 11 alphanumeric char. empty to disable'))."</td>
				</tr>
				<tr>
					<td>" . _('Module timezone') . "</td><td><input type=text size=5 maxlength=5 name=up_module_timezone value=\"" . $plugin_config['kannel']['module_timezone'] . "\"> "._hint(_('Eg: +0700 for Jakarta/Bangkok timezone'))."</td>
				</tr>
				<tr>
					<td>" . _('Incoming SMS time is in local time') . "</td><td><select name=up_local_time>".$option_local_time."</select> "._hint(_('Select no if the incoming SMS time is in UTC'))."</td>
				</tr>
				<tr>
					<td>" . _('Bearerbox hostname or IP') . "</td><td><input type=text size=30 maxlength=250 name=up_bearerbox_host value=\"" . $plugin_config['kannel']['bearerbox_host'] . "\"> "._hint(_('Kannel specific'))."</td>
				</tr>
				<tr>
					<td>" . _('Send SMS hostname or IP') . "</td><td><input type=text size=30 maxlength=250 name=up_sendsms_host value=\"" . $plugin_config['kannel']['sendsms_host'] . "\"> "._hint(_('Kannel specific'))."</td>
				</tr>
				<tr>
					<td>" . _('Send SMS port') . "</td><td><input type=text size=30 maxlength=10 name=up_sendsms_port value=\"" . $plugin_config['kannel']['sendsms_port'] . "\"> "._hint(_('Kannel specific'))."</td>
				</tr>
				<!-- Handle DLR config (emmanuel) -->
				<tr>
					<td>"._('Delivery Report')."</td><td>$up_dlr_box</td>
				</tr>
				<!-- end of Handle DLR config (emmanuel) -->
				<tr>
					<td>" . _('Additional URL parameter') . "</td><td><input type=text size=30 maxlength=250 name=up_additional_param value=\"" . $plugin_config['kannel']['additional_param'] . "\"></td>
				</tr>
				<tr>
					<td>" . _('playSMS web URL') . "</td><td><input type=text size=30 maxlength=250 name=up_playsms_web value=\"" . $plugin_config['kannel']['playsms_web'] . "\"> "._hint(_('URL to playSMS, empty it to set it to base URL'))."</td>
				</tr>
				<!-- Fixme Edward Added Kanel HTTP Admin Parameter-->
				<tr>
					<td>" . _('Kannel admin host') . "</td><td><input type=text size=30 maxlength=250 name=up_admin_host value=\"" . $plugin_config['kannel']['admin_host'] . " \"> "._hint(_('HTTP Kannel admin host'))."</td>
				</tr>
				<tr>
					<td>" . _('Kannel admin port') . "</td><td><input type=text size=30 maxlength=250 name=up_admin_port value=\"" . $plugin_config['kannel']['admin_port'] . "\"> "._hint(_('HTTP Kannel admin port'))."</td>
				</tr>
				<tr>
					<td>" . _('Kannel admin password') . "</td><td><input type=password size=30 maxlength=250 name=up_admin_password value=\"\"> "._hint(_('HTTP Kannel admin password'))."</td>
				</tr>
				<tr>
					<td>" . _('Kannel status') . "</td><td><textarea rows='20' style='height: 20em; width: 100%' disabled>".$kannel_status."</textarea></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td><input type='button' value=\""._('Restart Kannel')."\" class='button' onClick=\"parent.location.href='index.php?app=main&inc=gateway_kannel&op=manage_restart'\"></td>
				</tr>
				</tbody>
				<!-- End Of Fixme Edward Added Kanel HTTP Admin Parameter--> 
			</table>
			<p><input type=submit class=button value=\"" . _('Save') . "\">
			</form>";
		$content .= _back('index.php?app=main&inc=tools_gatewaymanager&op=gatewaymanager_list');
		_p($content);
		break;
	case "manage_save":
		$_SESSION['error_string'] = _('Changes has been made');
		// Handle DLR config (emmanuel)
		if (isset($_POST['dlr_box'])) {
			for ($i = 0, $c = count($_POST['dlr_box']); $i < $c; $i++) {
				$up_playsms_dlr += intval( $_POST['dlr_box'][$i] );
			}
		}
		// end of Handle DLR config (emmanuel)
		$items = array(
			'username' => $_POST['up_username'],
			'global_sender' => $_POST['up_global_sender'],
			'module_timezone' => $_POST['up_module_timezone'],
			'bearerbox_host' => $_POST['up_bearerbox_host'],
			'sendsms_host' => $_POST['up_sendsms_host'],
			'sendsms_port' => $_POST['up_sendsms_port'],
			'playsms_web' => $_POST['up_playsms_web'],
			'additional_param' => $_POST['up_additional_param'],
			'dlr' => $up_playsms_dlr,
			'admin_host' => $_POST['up_admin_host'],
			'admin_port' => $_POST['up_admin_port'],
			'local_time' => $_POST['up_local_time']
		);
		if ($_POST['up_password']) {
			$items['password'] = $_POST['up_password'];
		}
		if ($_POST['up_admin_password']) {
			$items['admin_password'] = $_POST['up_admin_password'];
		}
		registry_update(1, 'gateway', 'kannel', $items);
		header("Location: "._u('index.php?app=main&inc=gateway_kannel&op=manage'));
		exit();
		break;

	case "manage_restart":
		$admin_port = $plugin_config['kannel']['admin_port'];
		$admin_host = $plugin_config['kannel']['bearerbox_host'];
		$admin_host = ( $admin_port ? $admin_host.':'.$admin_port : $admin_host );
		$admin_password = $plugin_config['kannel']['admin_password'];
		$url = 'http://'.$admin_host.'/restart?password='.$admin_password;
		$restart = file_get_contents($url);
		$_SESSION['error_string']   = _('Restart Kannel').' - '._('Status').': '.$restart;
		header("Location: "._u('index.php?app=main&inc=gateway_kannel&op=manage'));
		exit();
		break;
}
