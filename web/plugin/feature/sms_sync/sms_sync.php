<?php
defined('_SECURE_') or die('Forbidden');

if (!auth_isvalid()) {
	auth_block();
};

// error messages
$error_content = '';
if ($err = $_SESSION['error_string']) {
	$error_content = "<div class=error_string>$err</div>";
}

switch (_OP_) {
	case "sms_sync_list":
		$list = registry_search($user_config['uid'], 'feature', 'sms_sync');
		$sms_sync_secret = $list['feature']['sms_sync']['secret'];
		if ($list['feature']['sms_sync']['enable']) {
			$option_enable = 'checked';
		}
		$sync_url = $core_config['http_path']['base'].'/plugin/feature/sms_sync/sync.php?uid='.$user_config['uid'];
		unset($tpl);
		$tpl = array(
			'name' => 'sms_sync',
			'var' => array(
				'ERROR' => $error_content,
				'HINT_SECRET' => _hint(_('Secret key is used in SMSSync app')),
				'HINT_ENABLE' => _hint(_('Check to enable receiving push messages from SMSSync app')),
				'SECRET' => $sms_sync_secret,
				'CHECKED' => $option_enable,
				'SYNC_URL' => $sync_url,
				'Manage sync' => _('Manage sync'),
				'Secret key' => _('Secret key'),
				'Enable SMS Sync' => _('Enable SMS Sync'),
				'Sync URL' => _('Sync URL'),
			'Notes' => _('Notes'),
			'Download SMSSync app for Android from' => _('Download SMSSync app for Android from'),
			'Save' => _('Save')
			)
		);
		_p(tpl_apply($tpl));
		break;
	case "sms_sync_save":
		$items['secret'] = $_POST['sms_sync_secret'];
		$items['enable'] = ( trim($_POST['sms_sync_enable']) ? 1 : 0 );
		if (registry_update($user_config['uid'], 'feature', 'sms_sync', $items)) {
			$_SESSION['error_string'] = _('SMS Sync configuration has been saved');
		} else {
			$_SESSION['error_string'] = _('Fail to save SMS Sync configuration');
		}
		header("Location: index.php?app=menu&inc=feature_sms_sync&op=sms_sync_list");
		exit();
		break;
}

?>
