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

if ($card_id = (int) $_REQUEST['card_id']) {
	$card = simplerate_getcard($card_id);
	if (!$card['name']) {
		$_SESSION['dialog']['danger'][] = sprintf(_('Card id:%d not found'), $card_id);

		header("Location: " . _u('index.php?app=main&inc=feature_simplerate&route=card&op=card_list'));
		exit();
	}
} else {
	$_SESSION['dialog']['danger'][] = sprintf(_('Card not found'), $card_id);

	header("Location: " . _u('index.php?app=main&inc=feature_simplerate&route=card&op=card_list'));
	exit();
}

switch (_OP_) {
	case "user_list":
		$tpl = [
			'name' => _OP_,
			'vars' => [
				'DIALOG_DISPLAY' => _dialog(),
				'Manage SMS rate' => _('Manage SMS rate'),
				'CARD_NAME' => $card['name'],
				'Manage user' => _('Manage user'),
				'Add user' => _button(_('index.php?app=main&inc=feature_simplerate&route=user&op=user_add&card_id=' . $card_id), _('Add user')),
				'User' => _('User'),
				'Name' => _('Name'),
				'Credit' => $icon_config['credit'],
				'Action' => _('Action'),
				'BUTTON_BACK' => _back('index.php?app=main&inc=feature_simplerate&route=card&op=card_list'),
			],
			'injects' => [
				'icon_config',
			]
		];

		// SELECT U.uid AS uid, username, name, credit, status FROM playsms_tblUser U
		// LEFT JOIN playsms_featureSimplerate_card_user CR ON U.uid = CR.uid
		// WHERE CR.card_id = '$card_id'
		// ORDER BY status, username, name

		$list = dba_search(_DB_PREF_ . '_tblUser U', 'U.uid AS uid, username, name, credit, status', [
			'CR.card_id' => $card_id
		], '', [
			'ORDER BY' => 'status, username, name'
		], 'LEFT JOIN ' . _DB_PREF_ . '_featureSimplerate_card_user CR ON U.uid = CR.uid');

		foreach ( $list as $row ) {
			$action = _confirm(
				sprintf(_('Are you sure you want to remove user %s ?'), $row['username']),
				_u('index.php?app=main&inc=feature_simplerate&route=user&op=user_remove&card_id=' . $card_id . '&uid=' . $row['uid']),
				'user_remove'
			);

			$tpl['loops']['data'][] = array(
				'tr_class' => $tr_class,
				'username' => $row['username'],
				'name' => $row['name'],
				'credit' => core_display_credit($row['credit']),
				'icon_admin' => ($row['status'] == 2 ? $icon_config['admin'] : ''),
				'action' => $action,
			);
		}

		$content = tpl_apply($tpl);
		_p($content);
		break;

	case "user_add":
		$lastpost = [
			'username' => _lastpost('username'),
			'name' => _lastpost('name'),
			'user' => (float) _lastpost('user'),
		];
		$lastpost['user'] = core_display_credit($lastpost['user']);

		$tpl = [
			'name' => _OP_,
			'vars' => [
				'DIALOG_DISPLAY' => _dialog(),
				'Manage SMS rate' => _('Manage SMS rate'),
				'CARD_NAME' => $card['name'],
				'Add user' => _('Add user'),
				'FORM_URL' => _u('index.php?app=main&inc=feature_simplerate&route=user&op=user_add_save&card_id=' . $card_id),
				'CSRF_FORM' => _CSRF_FORM_,
				'User' => _('User'),
				'Save' => _('Save'),
				'BUTTON_BACK' => _back('index.php?app=main&inc=feature_simplerate&route=user&op=user_list&card_id=' . $card_id),
			],
			'injects' => [
				'lastpost',
			]
		];

		$content = tpl_apply($tpl);
		_p($content);
		break;

	case 'user_add_save':
		if (!(($username = $_REQUEST['username']))) {
			$_SESSION['dialog']['danger'][] = _('You must select user');

			header("Location: " . _u('index.php?app=main&inc=feature_simplerate&route=user&op=user_add&card_id=' . $card_id));
			exit();
		}

		if (!($uid = user_username2uid($username))) {
			$_SESSION['dialog']['danger'][] = _('User not found');

			header("Location: " . _u('index.php?app=main&inc=feature_simplerate&route=user&op=user_add&card_id=' . $card_id));
			exit();
		}

		if (
			dba_add(_DB_PREF_ . '_featureSimplerate_card_user', [
				'card_id' => $card_id,
				'uid' => $uid,
			])
		) {
			$_SESSION['dialog']['info'][] = sprintf(_('User %s has been added'), $username);

			// clear laspost for new input
			_lastpost_empty();
		} else {
			$_SESSION['dialog']['danger'][] = sprintf(_('Fail to add user %s to card %s'), $username, $card['name']);
		}

		header("Location: " . _u('index.php?app=main&inc=feature_simplerate&route=user&op=user_add&card_id=' . $card_id));
		exit();

	case "user_remove":
		if (!($uid = (int) $_REQUEST['uid'])) {
			$_SESSION['dialog']['danger'][] = sprintf(_('User not found'), $uid);

			header("Location: " . _u('index.php?app=main&inc=feature_simplerate&route=user&op=user_list&card_id=' . $card_id));
			exit();
		}

		if (
			dba_remove(_DB_PREF_ . '_featureSimplerate_card_user', [
				'card_id' => $card_id,
				'uid' => $uid,
			])
		) {
			$_SESSION['dialog']['info'][] = sprintf(_('User %s has been removed'), user_uid2username($uid));
		} else {
			$_SESSION['dialog']['danger'][] = sprintf(_('Fail to remove user id:%d'), $uid);
		}

		header("Location: " . _u('index.php?app=main&inc=feature_simplerate&route=user&op=user_list&card_id=' . $card_id));
		exit();
}