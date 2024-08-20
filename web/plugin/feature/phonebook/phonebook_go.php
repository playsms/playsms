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

$items = isset($_REQUEST['itemid']) ? $_REQUEST['itemid'] : [];

switch (_OP_) {
	case 'delete':
		foreach ( $items as $item ) {
			if (
				dba_remove(
					_DB_PREF_ . '_featurePhonebook',
					array(
						'uid' => $user_config['uid'],
						'id' => $item
					)
				)
			) {
				dba_remove(
					_DB_PREF_ . '_featurePhonebook_group_contacts',
					array(
						'pid' => $item
					)
				);
				$_SESSION['dialog']['info'][] = _('Selected contact has been deleted');
			}
		}
		break;
}

$gpid = 0;
$ops = explode('_', _OP_);
if (isset($ops[0]) && $ops[0] == 'move' && isset($ops[1]) && $ops[1]) {
	$gpid = (int) $ops[1];
}

if ($gpid && (dba_valid(_DB_PREF_ . '_featurePhonebook_group', 'id', $gpid))) {
	foreach ( $items as $item ) {
		if (dba_valid(_DB_PREF_ . '_featurePhonebook', 'id', $item)) {
			if (
				dba_remove(
					_DB_PREF_ . '_featurePhonebook_group_contacts',
					[
						'pid' => $item
					]
				) or dba_isavail(
					_DB_PREF_ . '_featurePhonebook_group_contacts',
					[
						'pid' => $item
					]
				)
			) {
				$data = [
					'pid' => $item,
					'gpid' => $gpid
				];
				if (dba_add(_DB_PREF_ . '_featurePhonebook_group_contacts', $data)) {
					$_SESSION['dialog']['info'][] = _('Selected contact moved to new group');
				}
			}
		}
	}
}

$search = themes_search_session();
$nav = themes_nav_session();

$ref = $search['url'] . '&search_keyword=' . $search['keyword'] . '&search_category=' . $search['category'] . '&page=' . $nav['page'] . '&nav=' . $nav['nav'];
header("Location: " . _u($ref));
exit();