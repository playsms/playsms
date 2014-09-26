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

// insert to left menu array
$menutab = $core_config['menutab']['features'];
$menu_config[$menutab][] = array(
	"index.php?app=main&inc=feature_sms_subscribe&op=sms_subscribe_list",
	_('Manage subscribe') 
);

$subscribe_icon_add_message = "<img src=\"" . $core_config['http_path']['themes'] . "/" . core_themes_get() . "/images/edit_action.gif\" alt=\"" . _('Add message') . "\" title=\"" . _('Add message') . "\" border=0>";
$subscribe_icon_view_members = "<img src=\"" . $core_config['http_path']['themes'] . "/" . core_themes_get() . "/images/view_action.gif\" alt=\"" . _('View members') . "\" title=\"" . _('View members') . "\" border=0>";
$subscribe_icon_view_messages = "<img src=\"" . $core_config['http_path']['themes'] . "/" . core_themes_get() . "/images/view_action.gif\" alt=\"" . _('View messages') . "\" title=\"" . _('View messages') . "\" border=0>";

$plugin_config['sms_subscribe']['durations'] = array(
	_('Unlimited') => 0,
	_('1 Day') => 1001,
	_('2 Days') => 1002,
	_('1 Week') => 101,
	_('2 Weeks') => 102,
	_('1 Month') => 1,
	_('6 Months') => 6 
);
