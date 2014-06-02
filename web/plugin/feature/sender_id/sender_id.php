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
		
		if ($err = $_SESSION['error_string']) {
			$error_content = "<div class=error_string>$err</div>";
		}
		$tpl = array(
			'name' => 'sender_id',
			'vars' => array(
				'ERROR' => $error_content,
				'FORM_TITLE' => _('Manage sender ID') ,
				'ADD_URL' => _u('index.php?app=main&inc=feature_sender_id&op=sender_id_add') ,
				'HTTP_PATH_THEMES' => _HTTP_PATH_THEMES_,
				'HINT_STATUS' => _hint('Click the status button to enable or disable status') ,
				'Sender ID' => _('Sender ID') ,
				'Username' => _('Username') ,
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
		if ($err = $_SESSION['error_string']) {
			$error_content = "<div class=error_string>$err</div>";
		}
		unset($tpl);
		$tpl = array(
			'name' => 'sender_id_add',
			'vars' => array(
				'ERROR' => $error_content,
				'FORM_TITLE' => _('Manage sender ID') ,
				'FORM_SUBTITLE' => _('Add sender ID') ,
				'ACTION_URL' => _u('index.php?app=main&inc=feature_sender_id&op=sender_id_add_yes') ,
				'BUTTON_BACK' => _back('index.php?app=main&inc=feature_sender_id&op=sender_id_list') ,
				'HTTP_PATH_THEMES' => _HTTP_PATH_THEMES_,
				'Sender ID' => _mandatory('Sender ID') ,
				'Description' => _('Description') ,
			) ,
			'injects' => array(
				'icon_config'
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
		$sender_id = array(
			$_REQUEST['sender_id'] => 0
		);
		$description = array(
			$_REQUEST['sender_id'] => $_REQUEST['description']
		);
		registry_update($user_config['uid'], 'features', 'sender_id', $sender_id);
		registry_update($user_config['uid'], 'features', 'sender_id_desc', $description);
		
		$_SESSION['error_string'] = _('Sender ID has been save and waiting for approval') . ' (' . _('Sender ID') . ': ' . $_REQUEST['sender_id'] . ')';
		header("Location: " . _u('index.php?app=main&inc=feature_sender_id&op=sender_id_list'));
		exit();
		break;

	case "sender_id_edit":
		if ($err = $_SESSION['error_string']) {
			$error_content = "<div class=error_string>$err</div>";
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
				'ERROR' => $error_content,
				'FORM_TITLE' => _('Manage sender ID') ,
				'FORM_SUBTITLE' => _('Edit sender ID') ,
				'ACTION_URL' => _u('index.php?app=main&inc=feature_sender_id&op=sender_id_edit_yes') ,
				'BUTTON_BACK' => _back('index.php?app=main&inc=feature_sender_id&op=sender_id_list') ,
				'HTTP_PATH_THEMES' => _HTTP_PATH_THEMES_,
				'Sender ID' => _mandatory('Sender ID') ,
				'Description' => _('Description') ,
			) ,
			'injects' => array(
				'items',
				'icon_config'
			) ,
		);
		_p(tpl_apply($tpl));
		break;

	case "sender_id_edit_yes":
		$sender_id = array(
			$_REQUEST['sender_id'] => 0
		);
		$description = array(
			$_REQUEST['sender_id'] => $_REQUEST['description']
		);
		registry_update($_REQUEST['uid'], 'features', 'sender_id', core_sanitize_sender($sender_id));
		registry_update($_REQUEST['uid'], 'features', 'sender_id_desc', $description);
		
		$_SESSION['error_string'] = _('Sender ID has been updated') . ' (' . _('Sender ID') . ': ' . $_REQUEST['sender_id'] . ')';
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
