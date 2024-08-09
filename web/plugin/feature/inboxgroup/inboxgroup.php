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

if (!auth_isadmin()) {
	auth_block();
}

// main
switch (_OP_) {
	case 'list':
		$tpl = [
			'vars' => [
				'DIALOG_DISPLAY' => _dialog(),
				'Group inbox' => _('Group inbox'),
				'Add group inbox' => _button('index.php?app=main&inc=feature_inboxgroup&op=add', _('Add group inbox')),
				'Receiver number' => _('Receiver number'),
				'Keywords' => _('Keywords'),
				'Members' => _('Members'),
				'Catch-all' => _('Catch-all'),
				'Status' => _('Status'),
				'Action' => _('Action')
			],
		];
		$data = inboxgroup_getdataall();
		$c_count = is_array($data) ? count($data) : 0;
		for ($i = 0; $i < $c_count; $i++) {
			$c_rid = $data[$i]['id'];
			$c_members = count(inboxgroup_getmembers($c_rid));
			$c_members = "<a href='" . _u('index.php?app=main&inc=feature_inboxgroup&route=members&op=members&rid=' . $c_rid) . "'>" . $c_members . "</a>";
			$c_catchall = count(inboxgroup_getcatchall($c_rid));
			$c_catchall = "<a href='" . _u('index.php?app=main&inc=feature_inboxgroup&route=catchall&op=catchall&rid=' . $c_rid) . "'>" . $c_catchall . "</a>";
			$c_status = $data[$i]['status'] ? "<a href='" . _u('index.php?app=main&inc=feature_inboxgroup&op=disable&rid=' . $c_rid) . "'><span class=status_enabled /></a>" : "<a href='" . _u('index.php?app=main&inc=feature_inboxgroup&op=enable&rid=' . $c_rid) . "'><span class=status_disabled /></a>";
			$c_action = "<a href='" . _u('index.php?app=main&inc=feature_inboxgroup&op=edit&rid=' . $c_rid) . "'>" . $icon_config['edit'] . "</a> ";
			$c_action .= "<a href='" . _u('index.php?app=main&inc=feature_inboxgroup&op=del&rid=' . $c_rid) . "'>" . $icon_config['delete'] . "</a> ";
			$tpl['loops']['data'][] = array(
				'tr_class' => $tr_class,
				'in_receiver' => _display($data[$i]['in_receiver']),
				'keywords' => str_replace(',', ', ', _display($data[$i]['keywords'])),
				'members' => $c_members,
				'catchall' => $c_catchall,
				'status' => $c_status,
				'action' => $c_action
			);
		}
		$tpl['name'] = 'inboxgroup_list';
		$content = tpl_apply($tpl);
		_p($content);
		break;

	case 'add':
		$tpl = [
			'name' => 'inboxgroup_add',
			'vars' => [
				'DIALOG_DISPLAY' => _dialog(),
				'Group inbox' => _('Group inbox'),
				'Add group inbox' => _('Add group inbox'),
				'Receiver number' => _('Receiver number'),
				'Keywords' => _('Keywords'),
				'Description' => _('Description'),
				'HINT_KEYWORDS' => _hint(_('Separate with comma for multiple items')),
				'HINT_RECEIVER_NUMBER' => _hint(_('For example a short code')),
				'Save' => _('Save'),
				'BACK' => _back('index.php?app=main&inc=feature_inboxgroup&op=list')
			],
		];
		_p(tpl_apply($tpl));
		break;

	case 'add_submit':
		$in_receiver = $_REQUEST['in_receiver'];
		$keywords = $_REQUEST['keywords'];
		$description = $_REQUEST['description'];
		if ($in_receiver && $keywords && $description) {
			if (inboxgroup_dataadd($in_receiver, $keywords, $description)) {
				$_SESSION['dialog']['info'][] = _('Group inbox has been added') . " (" . _('Number') . ": " . $in_receiver . ")";
			} else {
				$_SESSION['dialog']['info'][] = _('Fail to add group inbox') . " (" . _('Number') . ": " . $in_receiver . ")";
			}
		} else {
			$_SESSION['dialog']['info'][] = _('You must fill all fields');
		}
		header("Location: " . _u('index.php?app=main&inc=feature_inboxgroup&op=add'));
		exit();

	case 'edit':
		$rid = $_REQUEST['rid'];
		$data = inboxgroup_getdatabyid($rid);
		$data = _display($data);
		$in_receiver = $data['in_receiver'];
		$keywords = $data['keywords'];
		$description = $data['description'];
		$selected_1 = $data['exclusive'] ? 'selected' : '';
		if (!$selected_1) {
			$selected_0 = 'selected';
		}
		$option_exclusive = "<option value='1' " . $selected_1 . ">" . _('yes') . "</option><option value='0' " . $selected_0 . ">" . _('no') . "</option>";
		$tpl = [
			'name' => 'inboxgroup_edit',
			'vars' => [
				'DIALOG_DISPLAY' => _dialog(),
				'Group inbox' => _('Group inbox'),
				'Edit group inbox' => _('Edit group inbox'),
				'RID' => $rid,
				'Receiver number' => _('Receiver number'),
				'IN_RECEIVER' => $in_receiver,
				'Keywords' => _('Keywords'),
				'Description' => _('Description'),
				'Exclusive' => _('Exclusive'),
				'KEYWORDS' => $keywords,
				'DESCRIPTION' => $description,
				'OPTION_EXCLUSIVE' => $option_exclusive,
				'HINT_KEYWORDS' => _hint(_('Separate with comma for multiple items')),
				'HINT_EXCLUSIVE' => _hint(_('Restrict sender to regular members or catch-all members only')),
				'Save' => _('Save'),
				'BACK' => _back('index.php?app=main&inc=feature_inboxgroup&op=list')
			],
		];
		_p(tpl_apply($tpl));
		break;

	case 'edit_submit':
		$rid = $_REQUEST['rid'];
		$keywords = $_REQUEST['keywords'];
		$description = $_REQUEST['description'];
		$exclusive = $_REQUEST['exclusive'];
		$data = inboxgroup_getdatabyid($rid);
		$in_receiver = _display($data['in_receiver']);
		if ($rid && $in_receiver && $keywords && $description) {
			if (inboxgroup_dataedit($rid, $keywords, $description, $exclusive)) {
				$_SESSION['dialog']['info'][] = _('Group inbox has been edited') . " (" . _('Number') . ": " . $in_receiver . ")";
			} else {
				$_SESSION['dialog']['info'][] = _('Fail to edit group inbox') . " (" . _('Number') . ": " . $in_receiver . ")";
			}
		} else {
			$_SESSION['dialog']['info'][] = _('You must fill all fields');
		}
		header("Location: " . _u('index.php?app=main&inc=feature_inboxgroup&op=edit&rid=' . $rid));
		exit();

	case 'del':
		$rid = $_REQUEST['rid'];
		$data = inboxgroup_getdatabyid($rid);
		$data = _display($data);
		$in_receiver = $data['in_receiver'];
		$keywords = $data['keywords'];
		$description = $data['description'];
		$c_members = count(inboxgroup_getmembers($rid));
		$c_members = "<a href='" . _u('index.php?app=main&inc=feature_inboxgroup&route=members&op=members&rid=' . $rid) . "'>" . $c_members . "</a>";
		$c_catchall = count(inboxgroup_getcatchall($rid));
		$c_catchall = "<a href='" . _u('index.php?app=main&inc=feature_inboxgroup&route=catchall&op=catchall&rid=' . $rid) . "'>" . $c_catchall . "</a>";
		$c_status = $data['status'] ? "<span class=status_enabled />" : "<span class=status_disabled />";
		$tpl = [
			'name' => 'inboxgroup_del',
			'vars' => [
				'DIALOG_DISPLAY' => _dialog(),
				'Group inbox' => _('Group inbox'),
				'Delete group inbox' => _('Delete group inbox'),
				'RID' => $rid,
				'Receiver number' => _('Receiver number'),
				'Keywords' => _('Keywords'),
				'Description' => _('Description'),
				'Members' => _('Members'),
				'Catch-all' => _('Catch-all'),
				'Status' => _('Status'),
				'IN_RECEIVER' => $in_receiver,
				'KEYWORDS' => $keywords,
				'DESCRIPTION' => $description,
				'C_MEMBERS' => $c_members,
				'C_CATCHALL' => $c_catchall,
				'C_STATUS' => $c_status,
				'ARE_YOU_SURE' => _('Are you sure you want to delete this group inbox ?'),
				'Yes' => _('Yes'),
				'BACK' => _back('index.php?app=main&inc=feature_inboxgroup&op=list')
			],
		];
		_p(tpl_apply($tpl));
		break;

	case 'del_submit':
		$rid = $_REQUEST['rid'];
		$data = inboxgroup_getdatabyid($rid);
		$in_receiver = _display($data['in_receiver']);
		if ($rid && $in_receiver) {
			if (inboxgroup_datadel($rid)) {
				$_SESSION['dialog']['info'][] = _('Group inbox has been deleted') . " (" . _('Number') . ": " . $in_receiver . ")";
			} else {
				$_SESSION['dialog']['info'][] = _('Fail to delete group inbox') . " (" . _('Number') . ": " . $in_receiver . ")";
			}
		} else {
			$_SESSION['dialog']['info'][] = _('Receiver number does not exist');
		}
		header("Location: " . _u('index.php?app=main&inc=feature_inboxgroup&op=list&rid=' . $rid));
		exit();

	case 'enable':
		$rid = $_REQUEST['rid'];
		$data = inboxgroup_getdatabyid($rid);
		$in_receiver = _display($data['in_receiver']);
		if ($rid && $in_receiver) {
			if (inboxgroup_dataenable($rid)) {
				$_SESSION['dialog']['info'][] = _('Group inbox has been enabled') . " (" . _('Number') . ": " . $in_receiver . ")";
			} else {
				$_SESSION['dialog']['info'][] = _('Fail to enable group inbox') . " (" . _('Number') . ": " . $in_receiver . ")";
			}
		} else {
			$_SESSION['dialog']['info'][] = _('Receiver number does not exist');
		}
		header("Location: " . _u('index.php?app=main&inc=feature_inboxgroup&op=list&rid=' . $rid));
		exit();

	case 'disable':
		$rid = $_REQUEST['rid'];
		$data = inboxgroup_getdatabyid($rid);
		$in_receiver = _display($data['in_receiver']);
		if ($rid && $in_receiver) {
			if (inboxgroup_datadisable($rid)) {
				$_SESSION['dialog']['info'][] = _('Group inbox has been disabled') . " (" . _('Number') . ": " . $in_receiver . ")";
			} else {
				$_SESSION['dialog']['info'][] = _('Fail to disable group inbox') . " (" . _('Number') . ": " . $in_receiver . ")";
			}
		} else {
			$_SESSION['dialog']['info'][] = _('Receiver number does not exist');
		}
		header("Location: " . _u('index.php?app=main&inc=feature_inboxgroup&op=list&rid=' . $rid));
		exit();

}
