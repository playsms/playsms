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
	case "rate_list":
		$tpl = [
			'name' => _OP_,
			'vars' => [
				'DIALOG_DISPLAY' => _dialog(),
				'Manage SMS rate' => _('Manage SMS rate'),
				'CARD_NAME' => $card['name'],
				'Manage rate' => _('Manage rate'),
				'Add rate' => _button(_('index.php?app=main&inc=feature_simplerate&route=rate&op=rate_add&card_id=' . $card_id), _('Add rate')),
				'Destination' => _('Destination'),
				'Prefix' => _('Prefix'),
				'Action' => _('Action'),
				'BUTTON_BACK' => _back('index.php?app=main&inc=feature_simplerate&route=card&op=card_list'),
			],
			'injects' => [
				'icon_config',
			]
		];

		/*
		 * SELECT R.id AS id, dst, prefix, rate FROM playsms_featureSimplerate R
		 * LEFT JOIN playsms_featureSimplerate_card_rate CR ON R.id = CR.rate_id
		 * WHERE CR.card_id = '$card_id'
		 */
		$list = dba_search(_DB_PREF_ . '_featureSimplerate R', 'R.id AS id, dst, prefix, rate', [
			'CR.card_id' => $card_id
		], '', [
			'ORDER BY' => 'dst, prefix'
		], 'LEFT JOIN ' . _DB_PREF_ . '_featureSimplerate_card_rate CR ON R.id = CR.rate_id');

		foreach ( $list as $row ) {
			$action = "
				<a href='" . _u('index.php?app=main&inc=feature_simplerate&route=rate&op=rate_edit&card_id=' . $card_id . '&rate_id=' . $row['id']) . "'>" . $icon_config['edit'] . "</a>
				" . _confirm(sprintf(_('Are you sure you want to delete rate %s ?'), $row['dst']), _u('index.php?app=main&inc=feature_simplerate&route=rate&op=rate_delete&card_id=' . $card_id . '&rate_id=' . $row['id']), 'delete');

			$tpl['loops']['data'][] = array(
				'tr_class' => $tr_class,
				'dst' => $row['dst'],
				'prefix' => $row['prefix'],
				'rate' => core_display_credit($row['rate']),
				'action' => $action,
			);
		}

		$content = tpl_apply($tpl);
		_p($content);
		break;

	case "rate_add":
		$lastpost = [
			'dst' => _lastpost('dst'),
			'prefix' => _lastpost('prefix'),
			'rate' => (float) _lastpost('rate'),
		];
		$lastpost['rate'] = core_display_credit($lastpost['rate']);

		$tpl = [
			'name' => _OP_,
			'vars' => [
				'DIALOG_DISPLAY' => _dialog(),
				'Manage SMS rate' => _('Manage SMS rate'),
				'CARD_NAME' => $card['name'],
				'Add rate' => _('Add rate'),
				'FORM_URL' => _u('index.php?app=main&inc=feature_simplerate&route=rate&op=rate_add_save&card_id=' . $card_id),
				'CSRF_FORM' => _CSRF_FORM_,
				'Destination' => _('Destination'),
				'Prefix' => _('Prefix'),
				'Rate' => _('Rate'),
				'Save' => _('Save'),
				'BUTTON_BACK' => _back('index.php?app=main&inc=feature_simplerate&route=rate&op=rate_list&card_id=' . $card_id),
			],
			'injects' => [
				'icon_config',
				'lastpost',
			]
		];

		$content = tpl_apply($tpl);
		_p($content);
		break;

	case 'rate_add_save':
		if (!(($dst = $_REQUEST['dst']) && ($_REQUEST['prefix'] || (string) $_REQUEST['prefix'] === '0'))) {

			$_SESSION['dialog']['danger'][] = _('You must fill all fields');

			header("Location: " . _u('index.php?app=main&inc=feature_simplerate&route=rate&op=rate_add&card_id=' . $card_id));
			exit();
		}
		$prefix = (string) $_REQUEST['prefix'];
		$rate = (float) $_REQUEST['rate'];

		if (
			$rate_id = dba_add(_DB_PREF_ . '_featureSimplerate', [
				'dst' => $dst,
				'prefix' => (string) core_sanitize_numeric($prefix),
				'rate' => (float) $rate,
			])
		) {
			if (
				dba_add(_DB_PREF_ . '_featureSimplerate_card_rate', [
					'card_id' => $card_id,
					'rate_id' => $rate_id,
				])
			) {

				$_SESSION['dialog']['info'][] = sprintf(_('Rate %s has been added'), $dst);

				// clear laspost for new input
				_lastpost_empty();
			} else {
				$_SESSION['dialog']['danger'][] = sprintf(_('Fail to add rate %s to card id:%d'), $dst, $card_id);
			}
		} else {
			$_SESSION['dialog']['danger'][] = sprintf(_('Fail to add rate %s'), $dst);
		}

		header("Location: " . _u('index.php?app=main&inc=feature_simplerate&route=rate&op=rate_add&card_id=' . $card_id));
		exit();

	case "rate_edit":
		if (!($rate_id = (int) $_REQUEST['rate_id'])) {
			$_SESSION['dialog']['danger'][] = _('Rate not found');

			header("Location: " . _u('index.php?app=main&inc=feature_simplerate&route=rate&op=rate_list&card_id=' . $card_id));
			exit();
		}

		/*
		 * SELECT R.id AS id, dst, prefix, rate FROM playsms_featureSimplerate R
		 * LEFT JOIN playsms_featureSimplerate_card_rate CR ON R.id = CR.rate_id
		 * WHERE CR.card_id = '$card_id' AND CR.rate_id = '$rate_id'
		 * LIMIT 1
		 */
		if (
			$list = dba_search(_DB_PREF_ . '_featureSimplerate R', 'R.id AS id, dst, prefix, rate', [
				'CR.card_id' => $card_id,
				'CR.rate_id' => $rate_id,
			], '', [
				'LIMIT' => 1,
			], 'LEFT JOIN ' . _DB_PREF_ . '_featureSimplerate_card_rate CR ON R.id = CR.rate_id')
		) {
			$list = $list[0];
			$list['rate'] = core_display_credit($list['rate']);
		} else {
			$_SESSION['dialog']['danger'][] = sprintf(_('Rate id:%d not found'), $rate_id);

			header("Location: " . _u('index.php?app=main&inc=feature_simplerate&route=rate&op=rate_list&card_id=' . $card_id));
			exit();
		}

		$tpl = [
			'name' => _OP_,
			'vars' => [
				'DIALOG_DISPLAY' => _dialog(),
				'Manage SMS rate' => _('Manage SMS rate'),
				'CARD_NAME' => $card['name'],
				'Edit rate' => _('Edit rate'),
				'FORM_URL' => _u('index.php?app=main&inc=feature_simplerate&route=rate&op=rate_edit_save&card_id=' . $card_id . '&rate_id=' . $list['id']),
				'CSRF_FORM' => _CSRF_FORM_,
				'Destination' => _('Destination'),
				'Prefix' => _('Prefix'),
				'Rate' => _('Rate'),
				'Save' => _('Save'),
				'BUTTON_BACK' => _back('index.php?app=main&inc=feature_simplerate&route=rate&op=rate_list&card_id=' . $card_id),
			],
			'injects' => [
				'icon_config',
				'list',
			]
		];

		$content = tpl_apply($tpl);
		_p($content);
		break;

	case 'rate_edit_save':
		if (!($rate_id = (int) $_REQUEST['rate_id'])) {
			$_SESSION['dialog']['danger'][] = _('Rate not found');

			header("Location: " . _u('index.php?app=main&inc=feature_simplerate&route=rate&op=rate_list&card_id=' . $card_id));
			exit();
		}

		if (!(($dst = $_REQUEST['dst']) && ($_REQUEST['prefix'] || (string) $_REQUEST['prefix'] === '0'))) {

			$_SESSION['dialog']['danger'][] = _('You must fill all fields');

			header("Location: " . _u('index.php?app=main&inc=feature_simplerate&route=rate&op=rate_edit&card_id=' . $card_id . '&rate_id=' . $rate_id));
			exit();
		}
		$prefix = (string) $_REQUEST['prefix'];
		$rate = (float) $_REQUEST['rate'];

		if (
			dba_update(_DB_PREF_ . '_featureSimplerate', [
				'dst' => $dst,
				'prefix' => (string) core_sanitize_numeric($prefix),
				'rate' => (float) $rate,
			], [
				'id' => $rate_id,
			])
		) {
			$_SESSION['dialog']['info'][] = sprintf(_('Rate %s has been updated'), $dst);

			// clear laspost for new input
			_lastpost_empty();
		} else {
			$_SESSION['dialog']['danger'][] = sprintf(_('Rate %s is not updated'), $dst);
		}

		header("Location: " . _u('index.php?app=main&inc=feature_simplerate&route=rate&op=rate_edit&card_id=' . $card_id . '&rate_id=' . $rate_id));
		exit();

	case "rate_delete":
		if (!($rate_id = (int) $_REQUEST['rate_id'])) {
			$_SESSION['dialog']['danger'][] = sprintf(_('Rate id:%d not found'), $rate_id);

			header("Location: " . _u('index.php?app=main&inc=feature_simplerate&route=rate&op=rate_list&card_id=' . $card_id));
			exit();
		}

		if (
			$result = dba_remove(_DB_PREF_ . '_featureSimplerate', [
				'id' => $rate_id
			])
		) {
			dba_remove(_DB_PREF_ . '_featureSimplerate_card_rate', [
				'rate_id' => $rate_id
			]);
		}

		if ($result) {
			$_SESSION['dialog']['info'][] = sprintf(_('Rate id:%d has been deleted'), $rate_id);
		} else {
			$_SESSION['dialog']['danger'][] = sprintf(_('Fail to delete rate id:%d'), $rate_id);
		}

		header("Location: " . _u('index.php?app=main&inc=feature_simplerate&route=rate&op=rate_list&card_id=' . $card_id));
		exit();
}