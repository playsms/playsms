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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with playSMS.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('_SECURE_') or die('Forbidden');

if (!auth_isvalid()) {
	auth_block();
};

switch (_OP_) {
	case "sms_sync_list":
		$list = registry_search($user_config['uid'], 'feature', 'sms_sync');
		$sms_sync_secret = $list['feature']['sms_sync']['secret'];
		if ($list['feature']['sms_sync']['enable']) {
			$option_enable = 'checked';
		}
		$sync_url = $core_config['http_path']['base'] . '/plugin/feature/sms_sync/sync.php?uid=' . $user_config['uid'];
		unset($tpl);
		$tpl = array(
			'name' => 'sms_sync',
			'vars' => array(
				'DIALOG_DISPLAY' => _dialog(),
				'HINT_SECRET' => _hint(_('Secret key is used in SMSSync app')) ,
				'HINT_ENABLE' => _hint(_('Check to enable receiving push messages from SMSSync app')) ,
				'SECRET' => $sms_sync_secret,
				'CHECKED' => $option_enable,
				'SYNC_URL' => $sync_url,
				'Manage sync' => _('Manage sync') ,
				'Secret key' => _('Secret key') ,
				'Enable SMS Sync' => _('Enable SMS Sync') ,
				'Sync URL' => _('Sync URL') ,
				'Notes' => _('Notes') ,
				'Download SMSSync app for Android from' => _('Download SMSSync app for Android from') ,
				'Save' => _('Save')
			)
		);
		_p(tpl_apply($tpl));
		break;

	case "sms_sync_save":
		$items['secret'] = $_POST['sms_sync_secret'];
		$items['enable'] = (trim($_POST['sms_sync_enable']) ? 1 : 0);
		if (registry_update($user_config['uid'], 'feature', 'sms_sync', $items)) {
			$_SESSION['dialog']['info'][] = _('SMS Sync configuration has been saved');
		} else {
			$_SESSION['dialog']['info'][] = _('Fail to save SMS Sync configuration');
		}
		header("Location: " . _u('index.php?app=main&inc=feature_sms_sync&op=sms_sync_list'));
		exit();
		break;
}
