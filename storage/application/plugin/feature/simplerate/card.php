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

switch (_OP_) {
	case "card_list":
		$tpl = [
			'name' => _OP_,
			'vars' => [
				'DIALOG_DISPLAY' => _dialog(),
				'Manage SMS rate' => _('Manage SMS rate'),
				'Manage card' => _('Manage card'),
				'Add card' => _button(_('index.php?app=main&inc=feature_simplerate&route=card&op=card_add'), _('Add card')),
				'Name' => _('Name'),
				'Notes' => _('Notes'),
				'Last update' => _('Last update'),
				'Action' => _('Action')
			],
			'injects' => [
				'icon_config'
			]
		];

		$list = dba_search(_DB_PREF_ . '_featureSimplerate_card', '*', '', '', [
			'ORDER BY' => 'name'
		]);
		foreach ( $list as $row ) {
			$num_of_rate = dba_count(_DB_PREF_ . '_featureSimplerate R', [
				'CR.card_id' => $row['id']
			], '', '', 'LEFT JOIN ' . _DB_PREF_ . '_featureSimplerate_card_rate CR ON R.id = CR.rate_id');

			$num_of_user = dba_count(_DB_PREF_ . '_tblUser U', [
				'CR.card_id' => $row['id']
			], '', '', 'LEFT JOIN ' . _DB_PREF_ . '_featureSimplerate_card_user CR ON U.uid = CR.uid');

			$action = "
				<a href='" . _u('index.php?app=main&inc=feature_simplerate&route=card&op=card_edit&card_id=' . $row['id']) . "'>" . $icon_config['edit'] . "</a>
				" . _confirm(sprintf(_('Are you sure you want to delete card %s ?'), $row['name']), _u('index.php?app=main&inc=feature_simplerate&route=card&op=card_delete&card_id=' . $row['id']), 'delete');

			$tpl['loops']['data'][] = array(
				'tr_class' => $tr_class,
				'name' => $row['name'],
				'notes' => $row['notes'],
				'num_of_rate' => $num_of_rate,
				'num_of_user' => $num_of_user,
				'rate_list_url' => _u('index.php?app=main&inc=feature_simplerate&route=rate&op=rate_list&card_id=' . $row['id']),
				'user_list_url' => _u('index.php?app=main&inc=feature_simplerate&route=user&op=user_list&card_id=' . $row['id']),
				'last_update' => core_display_datetime($row['last_update']),
				'created' => core_display_datetime($row['created']),
				'action' => $action
			);
		}

		$content = tpl_apply($tpl);
		_p($content);
		break;

	case "card_add":
		$lastpost = [
			'name' => _lastpost('name'),
			'notes' => _lastpost('notes'),
		];

		$tpl = [
			'name' => _OP_,
			'vars' => [
				'DIALOG_DISPLAY' => _dialog(),
				'Manage SMS rate' => _('Manage SMS rate'),
				'Add card' => _('Add card'),
				'FORM_URL' => _u('index.php?app=main&inc=feature_simplerate&route=card&op=card_add_save'),
				'CSRF_FORM' => _CSRF_FORM_,
				'Name' => _('Name'),
				'Notes' => _('Notes'),
				'Save' => _('Save'),
				'BUTTON_BACK' => _back('index.php?app=main&inc=feature_simplerate&route=card&op=card_list')
			],
			'injects' => [
				'lastpost'
			]
		];

		$content = tpl_apply($tpl);
		_p($content);
		break;

	case 'card_add_save':
		if (!(($name = $_REQUEST['name']) && ($notes = $_REQUEST['notes']))) {
			$_SESSION['dialog']['danger'][] = _('You must fill all fields');

			header("Location: " . _u('index.php?app=main&inc=feature_simplerate&route=card&op=card_add'));
			exit();
		}

		$result = dba_add(_DB_PREF_ . '_featureSimplerate_card', [
			'name' => $name,
			'notes' => $notes,
			'created' => core_get_datetime(),
			'last_update' => core_get_datetime()
		]);

		if ($result) {
			$_SESSION['dialog']['info'][] = sprintf(_('Card %s has been added'), $name);
		} else {
			$_SESSION['dialog']['danger'][] = sprintf(_('Fail to add card %s'), $name);
		}

		header("Location: " . _u('index.php?app=main&inc=feature_simplerate&route=card&op=card_list'));
		exit();

	case "card_edit":
		if (!($card_id = (int) $_REQUEST['card_id'])) {
			$_SESSION['dialog']['danger'][] = sprintf(_('Card id:%d not found'), $card_id);

			header("Location: " . _u('index.php?app=main&inc=feature_simplerate&route=card&op=card_list'));
			exit();
		}

		$list = dba_search(_DB_PREF_ . '_featureSimplerate_card', '*', [
			'id' => $card_id
		]);
		$list = $list[0];
		$list['last_update'] = core_display_datetime(($list['last_update']));

		$tpl = [
			'name' => _OP_,
			'vars' => [
				'DIALOG_DISPLAY' => _dialog(),
				'Manage SMS rate' => _('Manage SMS rate'),
				'Edit card' => _('Edit card'),
				'FORM_URL' => _u('index.php?app=main&inc=feature_simplerate&route=card&op=card_edit_save&card_id=' . $card_id),
				'CSRF_FORM' => _CSRF_FORM_,
				'ID' => _('ID'),
				'Name' => _('Name'),
				'Notes' => _('Notes'),
				'Last update' => _('Last update'),
				'Save' => _('Save'),
				'BUTTON_BACK' => _back('index.php?app=main&inc=feature_simplerate&route=card&op=card_list')
			],
			'injects' => [
				'list'
			]
		];

		$content = tpl_apply($tpl);
		_p($content);
		break;

	case 'card_edit_save':
		if (!($card_id = (int) $_REQUEST['card_id'])) {
			$_SESSION['dialog']['danger'][] = sprintf(_('Card id:%d not found'), $card_id);

			header("Location: " . _u('index.php?app=main&inc=feature_simplerate&route=card&op=card_list'));
			exit();
		}

		if (!(($name = $_REQUEST['name']) && ($notes = $_REQUEST['notes']))) {
			$_SESSION['dialog']['danger'][] = _('You must fill all fields');

			header("Location: " . _u('index.php?app=main&inc=feature_simplerate&route=card&op=card_edit&card_id=' . $card_id));
			exit();
		}

		$result = dba_update(_DB_PREF_ . '_featureSimplerate_card', [
			'name' => $name,
			'notes' => $notes,
			'last_update' => core_get_datetime()
		], [
			'id' => $card_id
		]);

		if ($result) {
			$_SESSION['dialog']['info'][] = sprintf(_('Card %s has been updated'), $name);
		} else {
			$_SESSION['dialog']['danger'][] = sprintf(_('Fail to update card %s'), $name);
		}

		header("Location: " . _u('index.php?app=main&inc=feature_simplerate&route=card&op=card_edit&card_id=' . $card_id));
		exit();

	case "card_delete":
		if (!($card_id = (int) $_REQUEST['card_id'])) {
			$_SESSION['dialog']['danger'][] = sprintf(_('Card id:%d not found'), $card_id);

			header("Location: " . _u('index.php?app=main&inc=feature_simplerate&route=card&op=card_list'));
			exit();
		}

		$result = dba_remove(_DB_PREF_ . '_featureSimplerate_card', [
			'id' => $card_id
		]);

		if ($result) {
			$_SESSION['dialog']['info'][] = sprintf(_('Card id:%d has been deleted'), $card_id);
		} else {
			$_SESSION['dialog']['danger'][] = sprintf(_('Fail to delete card id:%d'), $card_id);
		}

		header("Location: " . _u('index.php?app=main&inc=feature_simplerate&route=card&op=card_list'));
		exit();
}