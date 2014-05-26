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
if (!auth_isadmin()) {
	auth_block();
};

switch (_OP_) {
	case "recvsms":
		
		$data = registry_search(1, 'recvsms', 'sandbox_forward_to', 'forward_to');
		$forward_to = array_unique(unserialize($data['recvsms']['sandbox_forward_to']['forward_to']));
		$select_users = themes_select_users_multi('uids', $forward_to);
		
		$tpl = array(
			'name' => 'recvsms',
			'vars' => array(
				'ERROR' => _err_display() ,
				'PAGE_TITLE' => _('Route incoming SMS') ,
				'ACTION_URL' => _u('index.php?app=main&inc=core_recvsms&op=recvsms_save') ,
				'HTTP_PATH_THEMES' => _HTTP_PATH_THEMES_,
				'Forward sandbox SMS to users' => _('Forward sandbox SMS to users') ,
			) ,
			'injects' => array(
				'select_users'
			) ,
		);
		_p(tpl_apply($tpl));
		break;

	case "recvsms_save":
		$item = array(
			'forward_to' => serialize(array_unique($_REQUEST['uids'])) ,
		);
		registry_update(1, 'recvsms', 'sandbox_forward_to', $item);
		_log('sandbox SMS forward to uid:' . $_REQUEST['uid'], 2, 'recvsms');
		$_SESSION['error_string'] = _('Incoming SMS route changes has been saved');
		header("Location: " . _u('index.php?app=main&inc=core_recvsms&op=recvsms'));
		exit();
		break;
}
