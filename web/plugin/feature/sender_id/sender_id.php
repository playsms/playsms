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

if ($_REQUEST['sender_id']) {
	$_REQUEST['sender_id'] = core_sanitize_sender($_REQUEST['sender_id']);
}

switch (_OP_) {
	case 'sender_id_list':
		
		$tpl = array(
			'name' => 'sender_id',
			'vars' => array(
				'ERROR' => _err_display() ,
				'FORM_TITLE' => _('Manage sender ID') ,
				'ADD_URL' => _u('index.php?app=main&inc=feature_sender_id&op=sender_id_add') ,
				'HTTP_PATH_THEMES' => _HTTP_PATH_THEMES_,
				'HINT_STATUS' => _hint('Click the status button to enable or disable status') ,
				'Sender ID' => _('Sender ID') ,
				'Username' => _('Username') ,
				'Last update' => _('Last update') ,
			) ,
			'ifs' => array(
				'isadmin' => auth_isadmin() ,
			) ,
			'loops' => array(
				'sender_id_list' => sender_id_list() ,
			) ,
			'injects' => array(
				'icon_config',
			) ,
		);
		_p(tpl_apply($tpl));
		break;

	case "sender_id_add":
		
		if (auth_isadmin()) {
			$select_yesno = _yesno('enabled', 0);
			$select_users = themes_select_users_single('uid', $user_config['uid']);
		}
		
		$tpl = array(
			'name' => 'sender_id_add',
			'vars' => array(
				'ERROR' => _err_display() ,
				'FORM_TITLE' => _('Manage sender ID') ,
				'FORM_SUBTITLE' => _('Add sender ID') ,
				'ACTION_URL' => _u('index.php?app=main&inc=feature_sender_id&op=sender_id_add_yes') ,
				'BUTTON_BACK' => _back('index.php?app=main&inc=feature_sender_id&op=sender_id_list') ,
				'HTTP_PATH_THEMES' => _HTTP_PATH_THEMES_,
				'input_tag' => 'required',
				'Sender ID' => _mandatory('Sender ID') ,
				'Description' => _('Description') ,
				'User' => _('User') ,
				'Sender ID is enabled' => _('Sender ID is enabled') ,
			) ,
			'ifs' => array(
				'isadmin' => auth_isadmin() ,
			) ,
			'injects' => array(
				'select_yesno',
				'select_users',
				'icon_config',
				'core_config',
			) ,
		);
		_p(tpl_apply($tpl));
		break;

	case "sender_id_add_yes":
		if (sender_id_check($_REQUEST['sender_id'])) {
			$_SESSION['error_string'] = _('Sender ID is not available') . ' (' . _('Sender ID') . ': ' . core_sanitize_sender($_REQUEST['sender_id']) . ')';
			header("Location: " . _u('index.php?app=main&inc=feature_sender_id&op=sender_id_list'));
			exit();
			break;
		}
		$enabled = (auth_isadmin() ? (int)$_REQUEST['enabled'] : 0);
		$sender_id = array(
			$_REQUEST['sender_id'] => $enabled,
		);
		$description = array(
			$_REQUEST['sender_id'] => $_REQUEST['description']
		);
		$uid = ((auth_isadmin() && $_REQUEST['uid']) ? $_REQUEST['uid'] : $user_config['uid']);
		registry_update($uid, 'features', 'sender_id', $sender_id);
		registry_update($uid, 'features', 'sender_id_desc', $description);
		
		if (auth_isadmin()) {
			$_SESSION['error_string'] = _('Sender ID description has been added') . ' (' . _('Sender ID') . ': ' . $_REQUEST['sender_id'] . ')';
		} else {
			$_SESSION['error_string'] = _('Sender ID has been added and waiting for approval') . ' (' . _('Sender ID') . ': ' . $_REQUEST['sender_id'] . ')';
		}
		header("Location: " . _u('index.php?app=main&inc=feature_sender_id&op=sender_id_list'));
		exit();
		break;

	case "sender_id_edit":
		
		if (auth_isadmin()) {
			$select_yesno = _yesno('enabled', 0);
			$select_users = themes_select_users_single('uid', $user_config['uid']);
		}
		
		$search_sender_id = array(
			'registry_family' => 'sender_id',
			'id' => $_REQUEST['id']
		);
		$sender_id = registry_search_record($search_sender_id);
		
		$items['sender_id'] = core_sanitize_sender($sender_id[0]['registry_key']);
		$items['uid'] = $sender_id[0]['uid'];
		
		if (!auth_isadmin() && $user_config['uid'] != $sender_id[0]['uid']) {
			auth_block();
		};
		
		$search_description = array(
			'registry_family' => 'sender_id_desc',
			'registry_key' => $sender_id[0]['registry_key'],
		);
		$description = registry_search_record($search_description);
		$items['description'] = $description[0]['registry_value'];
		
		unset($tpl);
		$tpl = array(
			'name' => 'sender_id_add',
			'vars' => array(
				'ERROR' => _err_display() ,
				'FORM_TITLE' => _('Manage sender ID') ,
				'FORM_SUBTITLE' => _('Edit sender ID') ,
				'ACTION_URL' => _u('index.php?app=main&inc=feature_sender_id&op=sender_id_edit_yes') ,
				'BUTTON_BACK' => _back('index.php?app=main&inc=feature_sender_id&op=sender_id_list') ,
				'HTTP_PATH_THEMES' => _HTTP_PATH_THEMES_,
				'input_tag' => 'readonly',
				'Sender ID' => _mandatory('Sender ID') ,
				'Description' => _('Description') ,
				'User' => _('User') ,
				'Sender ID is enabled' => _('Sender ID is enabled') ,
			) ,
			'ifs' => array(
				'isadmin' => auth_isadmin() ,
			) ,
			'injects' => array(
				'select_yesno',
				'select_users',
				'items',
				'icon_config',
				'core_config',
			) ,
		);
		_p(tpl_apply($tpl));
		break;

	case "sender_id_edit_yes":
		
		$enabled = (auth_isadmin() ? (int)$_REQUEST['enabled'] : 0);
		$sender_id = array(
			$_REQUEST['sender_id'] => $enabled,
		);
		$description = array(
			$_REQUEST['sender_id'] => $_REQUEST['description']
		);
		$uid = ((auth_isadmin() && $_REQUEST['uid']) ? $_REQUEST['uid'] : $user_config['uid']);
		registry_update($uid, 'features', 'sender_id', $sender_id);
		registry_update($uid, 'features', 'sender_id_desc', $description);
		
		$_SESSION['error_string'] = _('Sender ID description has been updated') . ' (' . _('Sender ID') . ': ' . $_REQUEST['sender_id'] . ')';
		header("Location: " . _u('index.php?app=main&inc=feature_sender_id&op=sender_id_list'));
		exit();
		break;

	case "toggle_status":
		if (!auth_isadmin()) {
			auth_block();
		};
		
		$search = array(
			'id' => $_REQUEST['id'],
			'registry_family' => 'sender_id'
		);
		foreach (registry_search_record($search) as $row) {
			$status = ($row['registry_value'] == 0) ? 1 : 0;
			$items[$row['registry_key']] = $status;
			registry_update($row['uid'], 'features', 'sender_id', $items);
		}
		
		$_SESSION['error_string'] = (($status == 1) ? _('Sender ID is now enabled') : _('Sender ID is now disabled')) . ' (' . _('Sender ID') . ': ' . $row['registry_key'] . ')';
		
		header("Location: " . _u('index.php?app=main&inc=feature_sender_id&op=sender_id_list'));
		exit();
		break;

	case "sender_id_delete":
		$search = array(
			'id' => $_REQUEST['id'],
			'registry_family' => 'sender_id',
		);
		$sender_id = registry_search_record($search);
		if (!auth_isadmin() && $user_config['uid'] != $sender_id[0]['uid']) {
			auth_block();
		};
		registry_remove($sender_id[0]['uid'], 'features', 'sender_id', $sender_id[0]['registry_key']);
		
		$_SESSION['error_string'] = _('Sender ID has been removed') . ' (' . _('Sender ID') . ': ' . $sender_id[0]['registry_key'] . ')';
		header("Location: " . _u('index.php?app=main&inc=feature_sender_id&op=sender_id_list'));
		exit();
		break;
}
