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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with playSMS. If not, see <http://www.gnu.org/licenses/>.
 */
defined('_SECURE_') or die('Forbidden');

if (!auth_isvalid()) {
	auth_block();
}

// check permission when $uid supplied
if ($_REQUEST['uid']) {
	if (!auth_isadmin() && $user_config['uid'] != $_REQUEST['uid']) {
		auth_block();
	}
	$uid = $_REQUEST['uid'];
}

// check permission when $id supplied
if ($_REQUEST['id']) {
	$search = array(
		'id' => $_REQUEST['id'],
		'registry_family' => 'sender_id' 
	);
	$data_sender_id = registry_search_record($search);
	if (!auth_isadmin() && $user_config['uid'] != $data_sender_id[0]['uid']) {
		auth_block();
	}
	$uid = $data_sender_id[0]['uid'];
}

// check permission if _OP_ == toggle_status
if (_OP_ == 'toggle_status') {
	if (!auth_isadmin()) {
		auth_block();
	}
}

// default uid
if (!$uid) {
	$uid = $user_config['uid'];
}

// sender ID
$c_sender_id = $_REQUEST['sender_id'];
if ($c_sender_id) {
	$c_sender_id = core_sanitize_sender($c_sender_id);
}

// sender ID description
$c_sender_id_description = (trim($_REQUEST['description']) ? trim($_REQUEST['description']) : $c_sender_id);

switch (_OP_) {
	case 'sender_id_list':
		$search_category = array(
			_('Username') => 'uid',
			_('Sender ID') => 'registry_key' 
		);
		$keyword_converter = array(
			'uid' => 'user_username2uid' 
		);
		$base_url = 'index.php?app=main&inc=core_sender_id&op=sender_id_list';
		$search = themes_search($search_category, $base_url, $keyword_converter);
		$conditions = array(
			'uid' => $user_config['uid'],
			'registry_family' => 'sender_id' 
		);
		if (auth_isadmin()) {
			unset($conditions['uid']);
		}
		$keywords = $search['dba_keywords'];
		$count = dba_count(_DB_PREF_ . '_tblRegistry', $conditions, $keywords);
		$nav = themes_nav($count, $search['url']);
		$extras = array(
			'ORDER BY' => 'uid',
			'LIMIT' => $nav['limit'],
			'OFFSET' => $nav['offset'] 
		);
		$list = dba_search(_DB_PREF_ . '_tblRegistry', '*', $conditions, $keywords, $extras);
		
		$sender_id_list = array();
		$i = $nav['top'];
		$j = 0;
		for ($j = 0; $j < count($list); $j++) {
			$username = (auth_isadmin() ? user_uid2username($list[$j]['uid']) : '');
			$status = (($list[$j]['registry_value'] == 1) ? "<span class=status_enabled></span>" : "<span class=status_disabled></span>");
			$toggle_status = ((auth_isadmin()) ? "<a href='" . _u('index.php?app=main&inc=core_sender_id&op=toggle_status&id=' . $list[$j]['id']) . "'>" . $status . "</a>" : $status);
			$action = "
				<a href='" . _u('index.php?app=main&inc=core_sender_id&op=sender_id_edit&id=' . $list[$j]['id']) . "'>" . $icon_config['edit'] . "</a>
				<a href=\"javascript: ConfirmURL('" . addslashes(_('Are you sure you want to delete sender ID') . ' ? (' . _('Sender ID') . ': ' . $list[$j]['registry_key'] . ')') . "','" . _u('index.php?app=main&inc=core_sender_id&op=sender_id_delete&id=' . $list[$j]['id']) . "')\">" . $icon_config['delete'] . "</a>
			";
			$sender_id_list[] = array(
				'username' => $username,
				'sender_id' => core_sanitize_sender($list[$j]['registry_key']),
				'sender_id_description' => sender_id_description($list[$j]['uid'], $list[$j]['registry_key']),
				'lastupdate' => core_display_datetime(core_convert_datetime($list[$j]['c_timestamp'])),
				'status' => $toggle_status,
				'action' => $action 
			);
		}
		
		$tpl = array(
			'name' => 'sender_id',
			'vars' => array(
				'DIALOG_DISPLAY' => _dialog(),
				'SEARCH_FORM' => $search['form'],
				'NAV_FORM' => $nav['form'],
				'FORM_TITLE' => _('Manage sender ID'),
				'ADD_URL' => _u('index.php?app=main&inc=core_sender_id&op=sender_id_add'),
				'HTTP_PATH_THEMES' => _HTTP_PATH_THEMES_,
				'HINT_STATUS' => _hint(_('Click the status button to enable or disable status')),
				'Sender ID' => _('Sender ID'),
				'Username' => _('Username'),
				'Last update' => _('Last update') 
			),
			'ifs' => array(
				'isadmin' => auth_isadmin() 
			),
			'loops' => array(
				'sender_id_list' => $sender_id_list 
			),
			'injects' => array(
				'icon_config' 
			) 
		);
		_p(tpl_apply($tpl));
		break;
	
	case "sender_id_add":
		$nav = themes_nav_session();
		$search = themes_search_session();
		if ($nav['url']) {
			$ref = $nav['url'] . '&search_keyword=' . $search['keyword'] . '&page=' . $nav['page'] . '&nav=' . $nav['nav'];
		} else {
			$ref = 'index.php?app=main&inc=core_sender_id&op=sender_id_list';
		}
		
		if (auth_isadmin()) {
			$select_approve = _yesno('approved', 0);
			$select_users = themes_select_users_single('uid', $user_config['uid']);
		}
		$select_default = _yesno('default', 0);
		
		$tpl = array(
			'name' => 'sender_id_add',
			'vars' => array(
				'DIALOG_DISPLAY' => _dialog(),
				'FORM_TITLE' => _('Manage sender ID'),
				'FORM_SUBTITLE' => _('Add sender ID'),
				'ACTION_URL' => _u('index.php?app=main&inc=core_sender_id&op=sender_id_add_yes'),
				'BUTTON_BACK' => _back($ref),
				'HTTP_PATH_THEMES' => _HTTP_PATH_THEMES_,
				'HINT_DEFAULT' => _hint(_('Only when the sender ID is approved')),
				'input_tag' => 'required',
				'Sender ID' => _mandatory(_('Sender ID')),
				'Description' => _('Description'),
				'User' => _('User'),
				'Approve sender ID' => _('Approve sender ID'),
				'Set as default' => _('Set as default') 
			),
			'ifs' => array(
				'isadmin' => auth_isadmin() 
			),
			'injects' => array(
				'select_default',
				'select_approve',
				'select_users',
				'icon_config',
				'core_config' 
			) 
		);
		_p(tpl_apply($tpl));
		break;
	
	case "sender_id_add_yes":
		if (sender_id_add($uid, $c_sender_id, $c_sender_id_description, $_REQUEST['default'], $_REQUEST['approved'])) {
			if (auth_isadmin()) {
				$_SESSION['dialog']['info'][] = _('Sender ID description has been added') . ' (' . _('Sender ID') . ': ' . $c_sender_id . ')';
			} else {
				$_SESSION['dialog']['info'][] = _('Sender ID has been added and waiting for approval') . ' (' . _('Sender ID') . ': ' . $c_sender_id . ')';
			}
		} else {
			$_SESSION['dialog']['info'][] = _('Sender ID is not available') . ' (' . _('Sender ID') . ': ' . $c_sender_id . ')';
		}
		
		header("Location: " . _u('index.php?app=main&inc=core_sender_id&op=sender_id_add'));
		exit();
		break;
	
	case "sender_id_edit":
		$nav = themes_nav_session();
		$search = themes_search_session();
		if ($nav['url']) {
			$ref = $nav['url'] . '&search_keyword=' . $search['keyword'] . '&page=' . $nav['page'] . '&nav=' . $nav['nav'];
		} else {
			$ref = 'index.php?app=main&inc=core_sender_id&op=sender_id_list';
		}
		
		$items['id'] = $_REQUEST['id'];
		$items['uid'] = $uid;
		$items['sender_id'] = $data_sender_id[0]['registry_key'];
		$items['description'] = sender_id_description($uid, $data_sender_id[0]['registry_key']);
		
		if (auth_isadmin()) {
			$select_approve = _yesno('approved', $data_sender_id[0]['registry_value']);
			$select_users = user_getfieldbyuid($uid, 'name') . ' (' . user_uid2username($uid) . ')';
		}
		$default_sender_id = sender_id_default_get($uid);
		$select_default = _yesno('default', (strtoupper($data_sender_id[0]['registry_key']) == strtoupper($default_sender_id) ? 1 : 0));
		
		$tpl = array(
			'name' => 'sender_id_add',
			'vars' => array(
				'DIALOG_DISPLAY' => _dialog(),
				'FORM_TITLE' => _('Manage sender ID'),
				'FORM_SUBTITLE' => _('Edit sender ID'),
				'ACTION_URL' => _u('index.php?app=main&inc=core_sender_id&op=sender_id_edit_yes'),
				'BUTTON_BACK' => _back($ref),
				'HTTP_PATH_THEMES' => _HTTP_PATH_THEMES_,
				'HINT_DEFAULT' => _hint(_('Only when the sender ID is approved')),
				'input_tag' => 'readonly',
				'Sender ID' => _mandatory(_('Sender ID')),
				'Description' => _('Description'),
				'User' => _('User'),
				'Approve sender ID' => _('Approve sender ID'),
				'Set as default' => _('Set as default') 
			),
			'ifs' => array(
				'isadmin' => auth_isadmin() 
			),
			'injects' => array(
				'select_default',
				'select_approve',
				'select_users',
				'items',
				'icon_config',
				'core_config' 
			) 
		);
		_p(tpl_apply($tpl));
		break;
	
	case "sender_id_edit_yes":
		if (sender_id_update($uid, $c_sender_id, $c_sender_id_description, $_REQUEST['default'], $_REQUEST['approved'])) {
			$_SESSION['dialog']['info'][] = _('Sender ID description has been updated') . ' (' . _('Sender ID') . ': ' . $c_sender_id . ')';
		} else {
			$_SESSION['dialog']['info'][] = _('Fail to update due to invalid sender ID') . ' (' . _('Sender ID') . ': ' . $c_sender_id . ')';
		}
		
		header("Location: " . _u('index.php?app=main&inc=core_sender_id&op=sender_id_edit&id=' . $_REQUEST['id']));
		exit();
		break;
	
	case "toggle_status":
		$search = array(
			'id' => $_REQUEST['id'],
			'registry_family' => 'sender_id' 
		);
		foreach (registry_search_record($search) as $row) {
			$status = ($row['registry_value'] == 0) ? 1 : 0;
			$items[$row['registry_key']] = $status;
			registry_update($row['uid'], 'features', 'sender_id', $items);
		}
		
		$_SESSION['dialog']['info'][] = (($status == 1) ? _('Sender ID is now approved') : _('Sender ID is now disabled')) . ' (' . _('Sender ID') . ': ' . $row['registry_key'] . ')';
		
		header("Location: " . _u('index.php?app=main&inc=core_sender_id&op=sender_id_list'));
		exit();
		break;
	
	case "sender_id_delete":
		$nav = themes_nav_session();
		$search = themes_search_session();
		$ref = $nav['url'] . '&search_keyword=' . $search['keyword'] . '&page=' . $nav['page'] . '&nav=' . $nav['nav'];
		
		$uid = ((auth_isadmin() && $data_sender_id[0]['uid']) ? $data_sender_id[0]['uid'] : $user_config['uid']);
		
		registry_remove($uid, 'features', 'sender_id', $data_sender_id[0]['registry_key']);
		registry_remove($uid, 'features', 'sender_id_description', $data_sender_id[0]['registry_key']);
		
		$default_sender_id = sender_id_default_get($uid);
		if (strtoupper($data_sender_id[0]['registry_key']) == strtoupper($default_sender_id)) {
			sender_id_default_set($data_sender_id[0]['uid'], '');
		}
		
		$_SESSION['dialog']['info'][] = _('Sender ID has been removed') . ' (' . _('Sender ID') . ': ' . $data_sender_id[0]['registry_key'] . ')';
		header("Location: " . _u($ref));
		exit();
		break;
}
