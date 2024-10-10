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
	case "firewall_list":
		$search_category = array(
			_('IP address') => 'ip_address'
		);
		$base_url = 'index.php?app=main&inc=feature_firewall&op=firewall_list';
		$search = themes_search($search_category, $base_url);
		$keywords = $search['dba_keywords'];
		$count = dba_count(_DB_PREF_ . '_featureFirewall', [], $keywords);
		$nav = themes_nav($count, $search['url']);
		$extras = [
			'ORDER BY' => 'ip_address',
			'LIMIT' => (int) $nav['limit'],
			'OFFSET' => (int) $nav['offset']
		];
		$list = dba_search(_DB_PREF_ . '_featureFirewall', '*', [], $keywords, $extras);

		$content = _dialog() . "
			<h2>" . _('Manage firewall') . "</h2>
			<p>" . $search['form'] . "</p>
			<form name=fm_firewall_list id=fm_firewall_list action='index.php?app=main&inc=feature_firewall&op=actions' method=post>
			" . _CSRF_FORM_ . "
			<div class=table-responsive>
			<table class=playsms-table-list>
				<thead>
					<tr>
						<td colspan=3>
							<div class=actions_box>
								<div class=pull-left>
									<a href='" . _u('index.php?app=main&inc=feature_firewall&op=firewall_add') . "'>" . $icon_config['add'] . "</a>
								</div>
								<script type='text/javascript'>
									$(document).ready(function() {
										$('#action_go').click(function(){
											$('#fm_firewall_list').submit();
										});
									});
								</script>
								<div class=pull-right>
									<select name=go class=search_input_category>
										<option value=>" . _('Select') . "</option>
										<option value=delete>" . _('Delete') . "</option>
									</select>
									<a href='#' id=action_go>" . $icon_config['go'] . "</a>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<th width=95%>" . _('Blocked IP address') . "</th>
						<th width=5%><input type=checkbox onclick=CheckUncheckAll(document.fm_firewall_list)></th>
					</tr>
				</thead>
			<tbody>";

		$list = _display($list);
		foreach ( $list as $db_row ) {
			$pid = $db_row['id'];
			$ip_address = $db_row['ip_address'];
			$content .= "
				<tr>
					<td>$ip_address</td>
					<td>
						<input type=checkbox name=itemid[] value=\"$pid\">
					</td>
				</tr>";
		}

		$content .= "
				</tbody>
			</table>
			</div>
			<div class=pull-right>" . $nav['form'] . "</div>
			</form>";

		_p($content);
		break;

	case "actions":
		$is_removed = false;
		$items = isset($_REQUEST['itemid']) ? $_REQUEST['itemid'] : [];
		$go = $_REQUEST['go'];
		switch ($go) {
			case 'delete':
				foreach ( $items as $item ) {
					if (dba_remove(_DB_PREF_ . '_featureFirewall', ['id' => $item])) {
						$is_removed = true;
					}
				}
				break;
		}

		$search = themes_search_session();
		$nav = themes_nav_session();

		if ($is_removed) {
			$_SESSION['dialog']['info'][] = _('IP addresses have been deleted');
		}
		$ref = $search['url'] . '&search_keyword=' . $search['keyword'] . '&search_category=' . $search['category'] . '&page=' . $nav['page'] . '&nav=' . $nav['nav'];
		header("Location: " . _u($ref));
		exit();

	case "firewall_add":
		$content = _dialog() . "
			<h2>" . _('Manage firewall') . "</h2>
			<h3>" . _('Add blocked IP addresses') . " " . _hint(_('Multiple IP addresses must be comma-separated')) . "</h3>
			<form action='index.php?app=main&inc=feature_firewall&op=firewall_add_yes' method='post'>
			" . _CSRF_FORM_ . "
			<table class=playsms-table>
			<tr>
				<td class=label-sizer>" . _mandatory(_('IP addresses')) . "</td>
				<td><input type=text name='add_ip_address' required> " . _hint(_('Comma separated values for multiple IP addresses')) . "
				</td>
			</tr>
			</table>
			<p><input type='submit' class='button' value='" . _('Save') . "'></p>
			</form>
			" . _back('index.php?app=main&inc=feature_firewall&op=firewall_list');
		_p($content);
		break;

	case "firewall_add_yes":
		$add_ip_address = preg_replace('/[^\da-f\/:.,]+/i', '', $_POST['add_ip_address']);
		if ($add_ip_address) {
			$ip_addresses = explode(',', $add_ip_address);
			$ip_addresses = array_unique($ip_addresses);
			sort($ip_addresses);
			foreach ( $ip_addresses as $ip ) {
				if (core_net_check($ip)) {
					blacklist_addip($ip);
				}
			}
			$_SESSION['dialog']['info'][] = _('IP addresses have been blocked');
		} else {
			$_SESSION['dialog']['danger'][] = _('You must fill all fields');
		}
		header("Location: " . _u('index.php?app=main&inc=feature_firewall&op=firewall_add'));
		exit();
}
