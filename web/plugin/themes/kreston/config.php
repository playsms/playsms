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

// default loaded page/plugin
/*
 * $themes_config['default']['main'] = array( 'default_inc' => 'feature_report', 'default_route' => 'user', 'default_op' => '', );
 */

// override common action icons
/*
 * $themes_config['default']['icon'] = array( 'edit' => "<span class='playsms-icon glyphicon glyphicon-cog' alt='"._('Edit')."' title='"._('Edit')."'></span>", 'delete' => "<span class='playsms-icon glyphicon glyphicon-trash' alt='"._('Delete')."' title='"._('Delete')."'></span>", );
 */

// override menus
/*
$themes_config['default']['menu'] = array(
	$core_config['menutab']['my_account'] => array(
		array(
			'index.php?app=main&inc=feature_report&route=user_inbox&op=user_inbox',
			_('Inbox'),
			1 
		),
		array(
			'index.php?app=main&inc=core_sendsms&op=sendsms',
			_('Compose message'),
			2 
		),
		array(
			'index.php?app=main&inc=core_user&route=user_pref&op=user_pref',
			_('Preferences'),
			4 
		) 
	),
	$core_config['menutab']['settings'] => [],
	$core_config['menutab']['features'] => [],
	$core_config['menutab']['reports'] => array(
		array(
			'index.php?app=main&inc=feature_report&route=user_outgoing&op=user_outgoing',
			_('My sent messages'),
			3 
		),
		array(
			'index.php?app=main&inc=feature_report&route=credit&op=credit_list',
			_('My credit transactions'),
			1 
		) 
	) 
);
*/
