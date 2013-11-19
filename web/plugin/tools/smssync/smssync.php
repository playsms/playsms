<?php
defined('_SECURE_') or die('Forbidden');

if(!isadmin()){forcenoaccess();};

// error messages
$error_content = '';
if ($err = $_SESSION['error_string']) {
	$error_content = "<div class=error_string>$err</div>";
}

switch ($op) {
	case "smssync_list":
		$list = registry_search('tools','smssync');
		$smssync_secret = $list['tools']['smssync']['secret'];
		if ($list['tools']['smssync']['enable']) {
			$option_enable = 'checked';
		}
		unset($tpl);
		$tpl = array(
			'name' => 'smssync',
			'var' => array(
				'ERROR' => $error_content,
				'HINT_SECRET' => _hint(_('Secret key is used in SMSSync app')),
				'HINT_ENABLE' => _hint(_('Check to enable receiving push messages from SMSSync app')),
				'SECRET' => $smssync_secret,
				'CHECKED' => $option_enable,
				'SMS Sync' => _('SMS Sync'),
				'Configure SMS Sync' => _("Configure SMS Sync"),
				'Secret key' => _('Secret key'),
				'Enable SMS Sync' => _('Enable SMS Sync'),
				'Save' => _('Save')
			)
		);
		echo tpl_apply($tpl);
		break;
	case "smssync_save":
		$items['secret'] = $_POST['smssync_secret'];
		$items['enable'] = ( trim($_POST['smssync_enable']) ? 1 : 0 );
		if (registry_update('tools', 'smssync', $items)) {
			$_SESSION['error_string'] = _('SMS Sync configuration has been saved');
		} else {
			$_SESSION['error_string'] = _('Fail to save SMS Sync configuration');
		}
		header("Location: index.php?app=menu&inc=tools_smssync&op=smssync_list");
		exit();
		break;
}

?>
