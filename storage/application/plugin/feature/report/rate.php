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

switch (_OP_) {
	case "rate_list":
		$tpl = [
			'name' => _OP_,
			'vars' => [
				'Report' => _('Report'),
				'My SMS rate' => _('My SMS rate'),
				'Card' => _('Card'),
				'Destination' => _('Destination'),
				'Prefix' => _('Prefix'),
			],
			'injects' => [
				'icon_config',
			]
		];

		$rates = rate_getbyuid($_SESSION['uid']);

		foreach ($rates as $rate) {
			$tpl['loops']['data'][] = array(
				'tr_class' => $tr_class,
				'dst' => $rate['dst'],
				'prefix' => $rate['prefix'],
				'rate' => core_display_credit($rate['rate']),
			);
		}
		$content = tpl_apply($tpl);
		_p($content);
		break;
}
